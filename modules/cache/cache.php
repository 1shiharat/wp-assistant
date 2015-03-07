<?php
/*
Plugin Name: Cache
Description: The cache module which I can easily implement.
Text Domain: wp-assistant
*/
namespace WP_Assistant\modules\cache;

use \WP_Assistant\modules\module;
use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;

class cache extends module{

	private $active;

	public $timer;
	public static $table_name = '';

	public function __construct( $parent ) {
		$this->parent = $parent;

		$this->timer  = microtime( true );
		$this->active = true;
		$this->set_table_name();

		add_action( 'wpa_settings_fields_after', array( $this, 'add_settings' ), 10, 1 );

		add_action( 'init', array( $this, 'init' ), 0 );

		$this->settings = get_option( 'assistant_cache_settings' );
		config::set( 'module_cache', true );
		if ( config::get( 'module_cache' ) ) {
			add_action( 'delete_post', array( $this, 'delete_post_cache' ) );
			add_action( 'post_updated', array( $this, 'delete_post_cache' ) );
			add_action( 'wp_set_comment_status', array( $this, 'delete_comment_cache' ) );
			add_action( 'wp_insert_comment', array( $this, 'delete_comment_cache' ) );
			add_action( 'trash_comment', array( $this, 'delete_comment_cache' ) );
			add_action( 'spam_comment', array( $this, 'delete_comment_cache' ) );
			add_action( 'edit_comment', array( $this, 'delete_comment_cache' ) );
		}
	}

	public function add_settings() {

		$this->parent->settings->add_section( 'page_cache', function () {
			_e( 'Page Cache', 'wp-assistant' );
		}, __( 'Page Cache Settings', 'wp-assistant' ) );

		// ログインしていないユーザーのみキャッシュを有効
		$this->parent->settings->add_field(
			'anonymous',
			__( 'Only Anonymous User.', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'anonymous',
					'default' => 1,
					'desc'    => __( 'anonymous user only or enable the cache.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'page_cache',
			1
		);

		$this->parent->settings->add_field(
			'avoid_urls',
			__( 'Regular expression of deny URL cache', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'avoid_urls',
					'default' => 0,
					'value'   => config::get_option( 'avoid_urls' ),
					'desc'    => __( 'Please enter a regular expression of deny URL cache.', 'wp-assistant' ),
				);
				helper::textarea( $args );
			},
			'page_cache',
			''
		);
	}

	/**
	 * テーブル名を設定
	 */
	public function set_table_name(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'assistant_cache';
		$this->table_name = $table_name;
		return $table_name;
	}

	/**
	 * コメントのキャッシュを削除
	 * コメントが更新された時にキャッシュを削除する
	 *
	 * @param $comment_id
	 */
	public function delete_comment_cache( $comment_id ) {
		global $wpdb;
		$comment_id += 0;
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT comment_post_ID FROM $wpdb->comments WHERE comment_ID = %d", $comment_id ) );
		$uri     = $this->post_uri( $post_id );
		$this->delete_cache( $uri );
	}

	/**
	 * 記事IDからURLを取得
	 *
	 * @param $post_id
	 * @return string
	 */
	static function post_uri( $post_id ) {
		$uri = get_permalink( $post_id );
		$a   = parse_url( $uri );
		unset( $a['scheme'], $a['host'], $a['fragment'] );
		if ( ! empty( $a['query'] ) ) {
			$a['query'] = '?' . $a['query'];
		}

		return implode( '', $a );
	}

	/**
	 * 記事のキャッシュを削除
	 *
	 * @param $post_id
	 */
	public function delete_post_cache( $post_id ) {
		$uri = $this->post_uri( $post_id );
		$this->delete_cache( $uri );
	}


	/**
	 * ページ読み込み時のアクション
	 * @return void
	 */
	public function init() {
		global $user_ID, $user_login;
		$uri = $_SERVER['REQUEST_URI'];

		/**
		 * 管理画面では何もしない
		 */
		if ( is_admin() ){
			return;
		}

		$u = explode( "\n", config::get_option( 'avoid_urls' ) );

		foreach ( $u as $v ) {
			$v = trim( $v );
			if ( $v && preg_match( "#{$v}#is", $uri, $m ) ) {
				$this->active = false;
				break;
			}
		}

		if ( $this->active
		     &&
		     0 == intval( config::get_option('anonymous' ) )
		     && $user_ID > 0 ) {
			$this->active = false;
		}

		/**
		 * メソッドがpostの時はキャッシュをオフに
		 */
		if ( $this->active ) {
			if ( $this->active && ! empty( $_POST ) && ! empty( $this->settings['post_method'] ) ) {
				$this->active = false;
			}
		}

		if ( $this->active ) {
			if ( ( $data = $this->get_cache( $uri ) ) !== false ) {
				$this->update_cache_settings();
				global $wpdb;
				echo $data . "\n<!-- Generated from WP Assistant cache in " . ( microtime( true ) - $this->timer ) . ' s. '
				     . ' DB queries count : ' . $wpdb->num_queries . ' -->';
				die();
			}

			$this->miss();
			ob_start( array( $this, 'call_back_ob' ) );
		}
	}

	/**
	 * 呼び出すべきキャッシュがなかった時に、
	 * キャッシュをDBへセットする。
	 * その後、データを返す
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function call_back_ob($data) {
		$this->set_cache( $_SERVER['REQUEST_URI'], $data );
		return $data;
	}

	/**
	 * キャッシュの設定を更新
	 */
	public function update_cache_settings() {
		if ( ! empty( $this->settings['doStat'] ) ) {
			$this->settings['hits'] += 1;
			update_option( 'assistant_cache_settings', $this->settings );
		}
	}


	public function miss() {
		if ( ! empty( $this->settings['doStat'] ) ) {
			$this->settings['miss'] += 1;
			update_option( 'assistant_cache_settings', $this->settings );
		}
	}

	/**
	 * URLからキーを生成し返す
	 *
	 * @param $uri
	 *
	 * @return string
	 */
	static function getkey( $uri ) {
		return md5( $uri );
	}

	/**
	 * URLからキャッシュを削除する
	 *
	 * @param $uri
	 */
	private function delete_cache( $uri ) {
		global $wpdb;
		$table_name = $this->table_name;
		$wpdb->query( "DELETE FROM $table_name WHERE debug LIKE '" . mysql_escape_string( $uri ) . "%%'" );
	}

	/**
	 * キャッシュをDBから取得
	 *
	 * @param $uri
	 *
	 * @return bool
	 */
	private function get_cache( $uri ) {
		global $wpdb, $user_ID;
		$key = $this->getkey( $uri );
		$user_ID += 0;
		$table_name = $this->table_name;
		$time = time();
		$r = $wpdb->get_row( $wpdb->prepare( "SELECT data FROM $table_name WHERE data_key = %s AND user_id = %d AND expiretime > %s", $key, $user_ID, $time ) );

		if ( $r === null ) {
			return false;
		} else {
			return $r->data;
		}
	}

	/**
	 * キャッシュをセットする
	 *
	 * @param $uri
	 * @param $data
	 *
	 * @return bool
	 */
	private function set_cache( $uri, $data ) {
		if ( empty( $data ) ) {
			return false;
		}
		global $wpdb, $user_ID;
		$key = $this->getkey( $uri );
		$wpdb->replace( $wpdb->prefix . 'assistant_cache', array(
			'data_key'    => $key,
			'data'   => $data,
			'user_id'        => $user_ID + 0,
			'debug'      => $uri,
			'expiretime' => time() + $this->settings['cache_lifetime']
		) );

		return true;
	}

	/**
	 * テーブルのメンテナンス
	 */
	public function maintenance_table() {
		global $wpdb;
		$t = time();
		if ( $this->settings['last-maintain'] + $this->settings['dbmaintain_period'] < $t ) {
			$wpdb->query( "DELETE FROM `static::table_name` WHERE expiretime < $t" );
			$wpdb->query( "OPTIMIZE TABLE `static::table_name`" );
			$this->settings['last-maintain'] = $t;
			update_option( 'assistant_cache_settings', $this->settings );
		}
	}


	/**
	 * プラグイン有効化時のアクション
	 */
	public static function activate() {
		global $wpdb;

		//create cache table
		$table_name = $wpdb->prefix . 'assistant_cache';
		$wpdb->init_charset();
		$charset = $wpdb->charset;
		$collate = ( $wpdb->collate ) ? $wpdb->collate : 'utf8_general_ci';
		$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS `%s`", $table_name ) );
		$wpdb->query( $wpdb->prepare( "
CREATE TABLE IF NOT EXISTS `$table_name` (
  `data_key` varchar(32) CHARACTER SET %s COLLATE %s NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` mediumtext CHARACTER SET $charset COLLATE $collate NOT NULL,
  `expiretime` int(11) NOT NULL,
  `debug` varchar(250) CHARACTER SET $charset COLLATE $collate NOT NULL,
  PRIMARY KEY (`data_key`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=$charset;", $charset, $collate ) );

		add_option( 'assistant_cache_settings', static::default_settings() );
	}

	/**
	 * デフォルトの設定
	 * @return array
	 */
	static function default_settings() {
		return array(
			'cache_lifetime'    => 3600,
			'dbmaintain_period' => 43200,
			'avoid_urls'        => '^/wp-admin/^/wp-login.php',
			'users_nocache'     => 0,
			'post_method'            => 1,
			'doStat'            => 1,
			'chTRACK'           => 1,
			'anonymous'            => 1,
			'last-maintain'     => time()
		);
	}

	/**
	 * プラグイン削除時の設定
	 */
	static function uninstall() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS `static::table_name`" );
	}

}
