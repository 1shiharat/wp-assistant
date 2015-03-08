<?php
/*
Plugin Name: Admin Post Nav
Description: In an article edit screen of the admin screen, enabling the next post, the previous post link.
Text Domain: wp-assistant
*/
namespace WP_Assistant\modules\adminPostNav;

use \WP_Assistant\modules\module;
use \WP_Assistant\inc\config;
use \WP_Assistant\inc\helper;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class adminPostNav extends module {

	/** @var string 前の記事へテキスト */
	private static $prev_text = '';

	/** @var string 次の記事へテキスト */
	private static $next_text = '';

	/** @var array 有効な記事ステータス */
	private static $post_statuses = array( 'draft', 'future', 'pending', 'private', 'publish' ); // Filterable later

	/** @var string 記事ステータスのSQL文 */
	private static $post_statuses_sql = '';

	/**
	 * 初期化
	 * 投稿編集画面にフックをかける
	 */
	public function __construct() {
		add_action( 'load-post.php', array( __CLASS__, 'register_post_page_hooks' ) );
	}


	/**
	 * 記事編集画面に追加するアクションフック
	 */
	public static function register_post_page_hooks() {

		/** 各ナビゲーションの初期値を設定*/
		self::$prev_text = __( '&larr; Previous', 'wp-assistant' );
		self::$next_text = __( 'Next &rarr;', 'wp-assistant' );

		/** アクションフックを登録 */
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_css' ) );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'add_js' ) );
		add_action( 'do_meta_boxes', array( __CLASS__, 'do_meta_box' ), 10, 3 );
	}

	/**
	 * 投稿メタボックスとしてナビゲーションを登録する
	 * @param $post_type
	 * @param $type
	 * @param $post
	 */
	public static function do_meta_box( $post_type, $type, $post ) {
		$post_types = get_post_types();
		if ( ! in_array( $post_type, $post_types ) ) {
			return;
		}

		$post_statuses           = static::$post_statuses;
		self::$post_statuses_sql = implode( ", ", array_map( 'static::esc_sql_comma', $post_statuses ) );
		$label                   = self::_get_post_type_label( $post_type );
		if ( in_array( $post->post_status, $post_statuses ) ) {
			add_meta_box( 'adminpostnav', sprintf( __( '%s Navigation', 'wp-assistant' ), ucfirst( $post_type ) ), array(
				__CLASS__,
				'add_meta_box'
			), $post_type, 'side', 'core' );
		}
	}

	/**
	 * sqlのサニタイズ
	 * @param $value
	 *
	 * @return string
	 */
	public static function esc_sql_comma( $value ) {
		return "'" . esc_sql( $value ) . "'";
	}

	/**
	 * Adds the content for the post navigation meta_box.
	 *
	 * @param object $object
	 * @param array $box
	 *
	 * @return void (Text is echoed.)
	 */
	public static function add_meta_box( $object, $box ) {
		global $post_ID;
		$display = '';

		$context = self::_get_post_type_label( $object->post_type );

		$prev = self::previous_post();
		if ( $prev ) {
			$post_title = strip_tags( get_the_title( $prev->ID ) ); /* If only the_title_attribute() accepted post ID as arg */
			$display .= '<a href="' . get_edit_post_link( $prev->ID ) . '" id="admin-post-nav-prev" title="' .
			            esc_attr( sprintf( __( 'Previous %1$s: %2$s', 'wp-assistant' ), $context, $post_title ) ) .
			            '" class="admin-post-nav-prev add-new-h2">' . self::$prev_text . '</a>';
		}

		$next = self::next_post();
		if ( $next ) {
			if ( ! empty( $display ) ) {
				$display .= ' ';
			}
			$post_title = strip_tags( get_the_title( $next->ID ) );
			$display .= '<a href="' . get_edit_post_link( $next->ID ) .
			            '" id="admin-post-nav-next" title="' .
			            esc_attr( sprintf( __( 'Next %1$s: %2$s', 'wp-assistant' ), $context, $post_title ) ) .
			            '" class="admin-post-nav-next add-new-h2">' . self::$next_text . '</a>';
		}

		$display = '<span id="admin-post-nav">' . $display . '</span>';
		$display = apply_filters( 'admin_post_nav', $display );
		echo $display;
	}

	/**
	 * 投稿タイプのラベルを取得
	 * @param $post_type
	 *
	 * @return string
	 */
	public static function _get_post_type_label( $post_type ) {
		$label            = $post_type;
		$post_type_object = get_post_type_object( $label );
		if ( is_object( $post_type_object ) ) {
			$label = $post_type_object->labels->singular_name;
		}
		return strtolower( $label );
	}

	/**
	 * CSSを出力
	 */
	public static function add_css() {
		echo <<<'HTML'
	<style type="text/css">
	#admin-post-nav {margin-left:20px;}
	#adminpostnav #admin-post-nav {margin-left:0;}
	h2 #admin-post-nav {font-size:0.6em;}
	.inside #admin-post-nav a {top:0;margin-top:4px;display:inline-block;}
	</style>
HTML;
	}

	/**
	 * jsを出力
	 * メタボックスで追加したナビゲーションを、
	 * 上部にもっていく
	 */
	public static function add_js() {
		echo <<<'JS'
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#admin-post-nav').appendTo($('h2'));
		$('#adminpostnav, label[for="adminpostnav-hide"]').hide();
	});
	</script>
JS;
	}

	/**
	 * メインクエリ
	 * 次の記事、前の記事を取得する
	 * @param string $type
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return bool
	 */
	public static function query( $type = '<', $offset = 0, $limit = 15 ) {
		global $post_ID, $wpdb;

		if ( $type != '<' ) {
			$type = '>';
		}
		$offset = (int) $offset;
		$limit  = (int) $limit;

		$post_type = esc_sql( get_post_type( $post_ID ) );

		if ( function_exists( 'is_post_type_hierarchical' )
		     && is_post_type_hierarchical( $post_type )
		) {
			$orderby = 'post_title';
		} else {
			$orderby = 'ID';
		}
		$orderby = esc_sql( $orderby, $post_type );
		$post    = get_post( $post_ID );

		$sort              = $type == '<' ? 'DESC' : 'ASC';
		$post_statuses_sql = static::$post_statuses_sql;
		$sql               = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = %s AND post_status IN ( $post_statuses_sql ) AND $orderby $type %s ORDER BY %s %s LIMIT %d, %d;", $post_type, $post->$orderby, $orderby, $sort, $offset, $limit );
		$posts             = $wpdb->get_results( $sql );
		$result            = false;
		if ( $posts ) {
			foreach ( $posts as $post ) {
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$result = $post;
					break;
				}
			}
			if ( ! $result ) {
				$offset += $limit;
				$limit += $limit;

				return self::query( $type, $offset, $limit );
			}
		}

		return $result;
	}


	/**
	 * 次の記事を取得する
	 * @return bool
	 */
	public static function next_post() {
		return self::query( '>' );
	}

	/**
	 * 前の記事を取得する
	 * @return bool
	 */
	public static function previous_post() {
		return self::query( '<' );
	}

}