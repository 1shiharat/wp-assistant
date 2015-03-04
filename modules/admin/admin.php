<?php
/**
 * =====================================================
 * 管理画面設定ページを構築
 * @package   WP_Assistant
 * @author    Grow Group
 * @license   gpl v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace WP_Assistant\modules\admin;

use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;

class admin {

	private static $instance = null;

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
		$this->option_setting_slug = config::get( 'prefix' ) . 'wpa_settings';
		$this->option_page_slug    = config::get( 'prefix' ) . 'options_page';
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'load-index.php', array( $this, 'hide_welcome_panel' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'wp_ajax_update_wpaupports_option', array( $this, 'update_wpaupports_option' ) );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * ダッシュボードに登録
	 */
	public function add_dashboard_widgets() {
		if ( config::get_option( 'dashboard_disp' ) ) {
			wp_add_dashboard_widget( 'wpa_dashboard_widget', get_bloginfo( 'title' ), function () {
				include( 'views/dashboard.php' );
			} );
			global $wp_meta_boxes;
			$normal_dashboard      = $wp_meta_boxes['dashboard']['normal']['core'];
			$example_widget_backup = array( 'wpa_dashboard_widget' => $normal_dashboard['wpa_dashboard_widget'] );
			unset( $normal_dashboard['wpa_dashboard_widget'] );
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
		$this->add_section(
			'general', '', __( 'General', 'wp-assistant' ) );
		$this->add_field(
			'feed_links',
			__( 'Feed link tags (rss)', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'feed_links',
					'default' => 0,
					'desc'    => __( 'Please set the on or off of the feed links that are output to wp_head.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'wp_generator',
			__( 'WordPress generator meta tag', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'wp_generator',
					'default' => 0,
					'desc'    => __( 'The output the WordPress version information to wp_head.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'wp_shortlink_wp_head',
			__( 'The output of the short link', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'wp_shortlink_wp_head',
					'default' => 0,
					'desc'    => __( 'The output a short link to wp_head.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'wpautop',
			__( 'Stop of automatic formatting', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'wpautop',
					'default' => 0,
					'desc'    => __( 'Please select whether to stop the automatic formatting.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);
		$this->add_field(
			'revision',
			__( 'Stop of revision control', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'revision',
					'default' => 0,
					'desc'    => __( 'You can choose to disable the revision control. * I only want to hide. The database will be accumulated.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);

		$this->add_field(
			'jquery',
			__( 'Load of the jQuery', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'jquery',
					'default' => 0,
					'desc'    => __( 'Please specify whether load the jQuery library of cdnjs.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'bootstrap',
			__( 'Load of The Bootstrap3 framework', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'bootstrap',
					'default' => 0,
					'desc'    => __( 'Please specify whether to load the Bootstrap framework', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			0
		);

		$this->add_field(
			'xmlrpc',
			__( 'Stop of xmlrpc', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'xmlrpc',
					'default' => 0,
					'desc'    => __( 'Please specify whether to disable the xmlrpc as a security measure.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);

		$this->add_field(
			'author_archive',
			__( 'Disable the author page', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'author_archive',
					'default' => 0,
					'desc'    => __( 'Please specify whether to disable the author archive as a security measure.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);

//		$this->add_field(
//			'disable_update',
//			__( 'Disable automatic updates', 'wp-assistant' ),
//			function () {
//				$args = array(
//					'id'      => 'disable_update',
//					'default' => 0,
//					'desc'    => __( 'Please specify whether to hide stops updating the WordPress and plugins.', 'wp-assistant' ),
//				);
//				helper::radiobox( $args );
//			},
//			'general',
//			0
//		);

		$this->add_field(
			'show_current_template',
			__( 'Show in the admin bar the current template name', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'show_current_template',
					'default' => 1,
					'desc'    => __( 'To view the template name in the management bar, please to ON', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'general',
			1
		);

		$this->add_field(
			'admin_page_nav',
			__( 'Enabling Admin post Navigation', 'wp-assistant' ),
			function () {
				$args = array(
					'id'      => 'admin_page_nav',
					'default' => 1,
					'desc'    => __( 'In an article edit screen of the management screen, enabling the next post, the previous post link.', 'wp-assistant' ),
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
			echo __( 'Please input the content to be displayed on the dashboard widget.', 'wp-assistant' );
		}, __( 'Replace Welcome Panel', 'wp-assistant' ) );

		$this->add_field(
			'dashboard_disp',
			__( 'Enabling original dashboard widget', 'wp-assistant' ),
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
			'',
			function () {
				_e( '<p>Please enter the content to be displayed on the dashboard.</p>', 'wp-assistant' );
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
		do_action( 'wpa_settings_fields_after', $this );


	}

	/**
	 * Setting APIを使用したオプションページの作成
	 * @return void
	 */
	public function add_admin_menu() {

		add_menu_page(
			__( 'WP Assistant', 'wp-assistant' ),
			__( 'WP Assistant', 'wp-assistant' ),
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

				wp_localize_script( config::get( 'prefix' ) . 'admin_scripts', 'wpaSETTINGS', array(
					'action'    => 'update_wpaupports_option',
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

		/**
		 * インストールされていない場合、デフォルトの設定を登録
		 */
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
	public function update_wpaupports_option() {

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

			$settings['dashboard_contents'] = esc_html( $form_array['dashboard_contents'] );
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