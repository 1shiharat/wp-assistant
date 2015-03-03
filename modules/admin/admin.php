<?php
/**
 * =====================================================
 * 管理画面設定ページを構築
 * @package   siteSupports
 * @author    Grow Group
 * @license   gpl v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace siteSupports\modules\admin;

use siteSupports\config;
use siteSupports\inc\helper;

class admin {

	/** @var string 設定ページのスラッグ */
	public $option_page_slug = '';

	public $option_setting_slug = '';

	public $prefix = '';

	/**
	 * 設定名
	 * @var string
	 */
	public $setting_section_names = '';

	public $setting_field_names = '';

	/**
	 * 初期化
	 */
	public function __construct() {
		$this->option_setting_slug = config::get( 'prefix' ) . 'ggs_settings';
		$this->option_page_slug    = config::get( 'prefix' ) . 'options_page';
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'load-index.php', array( $this, 'hide_welcome_panel' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'wp_ajax_update_ggsupports_option', array( $this, 'update_ggsupports_option' ) );
	}

	/**
	 * ダッシュボードに登録
	 */
	public function add_dashboard_widgets() {
		if ( config::get_option( 'dashboard_disp' ) ) {
			wp_add_dashboard_widget( 'ggs_dashboard_widget', get_bloginfo( 'title' ), function () {
				include( 'views/dashboard.php' );
			} );
			global $wp_meta_boxes;
			$normal_dashboard      = $wp_meta_boxes['dashboard']['normal']['core'];
			$example_widget_backup = array( 'ggs_dashboard_widget' => $normal_dashboard['ggs_dashboard_widget'] );
			unset( $normal_dashboard['ggs_dashboard_widget'] );
			$sorted_dashboard                             = array_merge( $example_widget_backup, $normal_dashboard );
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
		}
	}


	/**
	 * ウェルカムパネルを非表示に
	 * @return void
	 */
	function hide_welcome_panel() {
		$user_id = get_current_user_id();

		if ( 1 == get_user_meta( $user_id, 'show_welcome_panel', true ) ) {
			update_user_meta( $user_id, 'show_welcome_panel', 0 );
		}
	}

	/**
	 * 楽天API設定画面を作成
	 * @return void
	 */
	public function settings_init() {

		// 設定項目を登録
		register_setting( config::get( 'prefix' ) . '_settings', config::get( 'prefix' ) . '_options' );

		/**
		 * 1. サイト設定
		 */
		$this->add_section( 'general', '', __( 'サイトの設定', 'ggsupports' ) );
		$this->add_field(
			'feed_links',
			__( 'フィードリンク (RSS)', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'feed_links',
					'default' => 0,
					'desc'    => __( 'wp_head に出力されるフィードリンクのオンオフを設定してください。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'wp_generator',
			__( 'ジェネレーターメタタグの出力 (バージョン情報の出力)', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'wp_generator',
					'default' => 0,
					'desc'    => __( 'wp_head にWordPressのバージョン情報を出力します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'wp_shortlink_wp_head',
			__( 'ショートリンクの出力', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'wp_shortlink_wp_head',
					'default' => 0,
					'desc'    => __( 'wp_head にショートリンクを出力します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'wpautop',
			__( '自動整形の停止', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'wpautop',
					'default' => 0,
					'desc'    => __( '自動整形を停止します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);
		$this->add_field(
			'revision',
			__( 'リビジョンコントロールの停止', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'revision',
					'default' => 0,
					'desc'    => __( 'リビジョンコントロールを無効にすることができます。<br /> ※ 非表示にするのみです。データベースには蓄積されます。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);

		$this->add_field(
			'jquery',
			__( 'jQueryライブラリの読み込み', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'jquery',
					'default' => 0,
					'desc'    => __( 'WordPressに内包されているjQueryライブラリを読み込みます。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'bootstrap',
			__( 'Bootstrap3フレームワークの読み込み', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'bootstrap',
					'default' => 0,
					'desc'    => __( 'Bootstrap フレームワークを自動的に読み込みます。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'xmlrpc',
			__( 'xmlrpcの停止', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'xmlrpc',
					'default' => 0,
					'desc'    => __( 'セキュリティ対策としてxmlrpcを無効にします。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);

		$this->add_field(
			'author_archive',
			__( '著者アーカイブの無効', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'author_archive',
					'default' => 0,
					'desc'    => __( 'セキュリティ対策として著者アーカイブを無効にします。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);

		$this->add_field(
			'disable_update',
			__( '自動更新の無効化', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'disable_update',
					'default' => 0,
					'desc'    => __( 'WordPress本体、プラグインの更新を停止し非表示にします。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);
		$this->add_field(
			'show_current_template',
			__( '現在のテンプレート名を管理バーに表示', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'show_current_template',
					'default' => 1,
					'desc'    => __( 'サイトフロント画面にて、現在表示されているテンプレート名を出力します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);
		$this->add_field(
			'admin_page_nav',
			__( '管理画面編集ナビの有効化', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'admin_page_nav',
					'default' => 1,
					'desc'    => __( '管理画面の記事編集画面で、次の記事、前の記事リンクを表示します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);
		/**
		 * 2 ダッシュボードウィジェット
		 */
		$this->add_section( 'dashboard', function () {
			echo __( 'ダッシュボードウィジェットに表示するコンテンツを入力してください。', 'ggsupports' );
		}, __( 'ダッシュボードウィジェット', 'ggsupports' ) );

		$this->add_field(
			'dashboard_disp',
			__( 'ダッシュボードウィジェットの有効化', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'dashboard_disp',
					'default' => 0,
					'desc'    => '',
				);
				helper::radiobox( $args );
			},
			'dashboard',
			0
		);

		$this->add_field(
			'dashboard_contents',
			__( '', 'ggsupports' ),
			function () {
				_e( '<p>ダッシュボードに表示させるコンテンツを入力してください。</p>', 'ggsupports' );
				$dashboard_contents = config::get_option( 'dashboard_contents' );
				$editor_settings    = array(
					'wpautop'             => false,
					'media_buttons'       => true,
					'default_editor'      => '',
					'drag_drop_upload'    => true,
					'textarea_name'       => 'dashboard_contents',
					'textarea_rows'       => 50,
					'tabindex'            => '',
					'tabfocus_elements'   => ':prev,:next',
					'editor_css'          => '',
					'editor_class'        => '',
					'teeny'               => false,
					'dfw'                 => false,
					'_content_editor_dfw' => false,
					'tinymce'             => false,
					'quicktags'           => true
				);
				wp_editor( $dashboard_contents, 'dashboard_contents', $editor_settings );
			},
			'dashboard',
			''
		);

		/**
		 * 他のモジュールから拡張可能なようにアクションフックを仕掛ける
		 */
		do_action( 'ggs_settings_fields_after', $this );


	}

	/**
	 * Setting APIを使用したオプションページの作成
	 * @return void
	 */
	public function add_admin_menu() {

		add_menu_page(
			__( '制作サポート', 'ggsupports' ),
			__( '制作サポート', 'ggsupports' ),
			'manage_options',
			$this->option_page_slug,
			array(
				$this,
				'option_page'
			)
		);

	}

	/**
	 * オプションページのレンダリング
	 * @return void
	 */
	public function option_page() {
		$nonce = wp_create_nonce( __FILE__ );
		include "views/options.php";
	}

	/**
	 * ダッシュボード用のjsを埋め込み
	 *
	 * @param  string $hook 呼び出されるファイル名
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {

		switch ( $hook ) {
			case 'index.php' :
			case 'toplevel_page_' . config::get( 'prefix' ) . 'options_page' :
				wp_enqueue_script( config::get( 'prefix' ) . 'admin_scripts', config::get( 'plugin_url' ) . 'assets/js/plugins.min.js', array(
					'jquery',
					'jquery-ui-tabs',
					'jquery-ui-button',
					'jquery-ui-accordion',
				), config::get( 'version' ) );

				wp_localize_script( config::get( 'prefix' ) . 'admin_scripts', 'GGSSETTINGS', array(
					'action'    => 'update_ggsupports_option',
					'_wp_nonce' => wp_create_nonce( __FILE__ )
				) );
				break;
		}

		wp_enqueue_style( 'jquery-ui-smoothness', config::get( 'plugin_url' ) . 'assets/css/plugins.min.css', config::get( 'version' ), config::get( 'version' ) );

	}

	/**
	 * Setting API add_settings_section のラッパー
	 *
	 * @param $name
	 * @param $title
	 */
	public function add_section( $name, $title, $tabs_name ) {

		$section_name = $name . '_section';
		add_settings_section(
			$section_name,
			$title,
			'',
			$this->option_page_slug
		);

		$this->setting_section_names[ $section_name ] = $tabs_name;

	}

	/**
	 * Setting API add_setting_field のワッパー
	 *
	 * @param $name
	 * @param $title
	 * @param $callback
	 * @param $section
	 *
	 * @param string $desc
	 */
	public function add_field( $name, $title, $callback, $section, $default = 0 ) {

		if ( $title ) {
			$title = '<h3><span class="dashicons dashicons-arrow-right-alt2"></span> ' . $title . '</h3>';
		}

		add_settings_field(
			$name,
			$title,
			$callback,
			$this->option_page_slug,
			$section . '_section'
		);

		$this->setting_field_names[] = $name;

		if ( ! config::get( 'install' ) ) {
			$options          = get_option( config::get( 'prefix' ) . 'options' );
			$options[ $name ] = $default;
			update_option( config::get( 'prefix' ) . 'options', $options );
		}

	}

	/**
	 * Ajax で受けた情報を保存
	 * @return void
	 */
	public function update_ggsupports_option() {

		if ( ! wp_verify_nonce( $_REQUEST['_wp_nonce'], __FILE__ ) ) {
			echo 0;
			exit();
		}

		$form_str = urldecode( $_REQUEST['form'] );
		parse_str( $form_str, $form_array );

		/**
		 * 値が有効な場合、値を照合してサニタイズ後オプションを更新
		 */
		if ( $form_array ) {
			$settings = array_map( array( $this, 'sanitizes_fields' ), $form_array );

			/**
			 * add_fieldで追加したinput以外は受け付けない
			 */
			foreach ( $settings as $settting_key => $setting ) {
				if ( ! in_array( $settting_key, $this->setting_field_names ) ) {
					unset( $settings[ $settting_key ] );
				}
			}

			$settings['dashboard_contents'] = $form_array['dashboard_contents'];
			echo update_option( config::get( 'prefix' ) . 'options', $settings );
			exit();
		}
		echo 0;
		exit();
	}

	/**
	 * 無害化
	 *
	 * @param $fields
	 *
	 * @return array|string
	 */
	public function sanitizes_fields( $fields ) {
		if ( is_array( $fields ) ) {
			return array_map( 'sanitize_text_field', $fields );
		}

		return sanitize_text_field( $fields );
	}


}