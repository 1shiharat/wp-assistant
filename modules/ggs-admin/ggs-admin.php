<?php

/**
 * Class Ggs_Admin
 * 管理画面用クラス
 */
class Ggs_Admin {

	public $option_page_slug = '';
	public $option_setting_slug = '';
	public $prefix = '';

	public function __construct() {
		$this->option_setting_slug = 'ggsupports_settings';
		$this->option_page_slug    = 'ggsupports_options_page';
		$this->prefix              = 'ggsupports_';
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'load-index.php', array( $this, 'hide_welcome_panel' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/**
	 * ダッシュボードに登録
	 */
	public function add_dashboard_widgets() {
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
		register_setting( 'ggsupports_settings', 'ggsupports_options' );

		/**
		 * 1. サイト設定
		 */
		$this->add_section( 'general', '' );
		$this->add_field(
			'feed_links',
			__( 'フィードリンク (RSS)', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_feed_links',
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'wp_generator',
			__( 'ジェネレーターメタタグの出力 (バージョン情報の出力)', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wp_generator',
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'wp_shortlink_wp_head',
			__( 'ショートリンクの出力', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wp_shortlink_wp_head',
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);


		$this->add_field(
			'wpautop',
			__( 'ショートリンクの出力', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wpautop',
					'default' => 0,
					'desc' => '',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'wpautop',
			__( '自動整形の停止', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wpautop',
					'default' => 0,
					'desc' => '記事を出力する際の自動整形を停止します。',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'revision',
			__( 'リビジョンコントロールの停止', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_revision',
					'default' => 0,
					'desc' => '投稿、固定ページのリビジョン管理のを無効にすることができます。',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'jquery',
			__( 'jQueryライブラリの読み込み', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_jquery',
					'default' => 0,
					'desc' => 'WordPressに内包されているjQueryライブラリを読み込みます。',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'bootstrap',
			__( 'Bootstrap3フレームワークの読み込み', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_bootstrap',
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general',
			'Bootstrap フレームワークを自動的に読み込みます。'
		);

		$this->add_field(
			'admin_bar_disp',
			__( '管理バーの停止', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_admin_bar_disp',
					'default' => 1,
					'desc' => '管理バーを停止します。',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'xmlrpc',
			__( 'xmlrpcの停止', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_xmlrpc',
					'default' => 0,
					'desc' => 'セキュリティ対策としてxmlrpcを無効にします。',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'aunthor_archive',
			__( '著者アーカイブの無効', '' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_aunthor_archive',
					'default' => 0,
					'desc' => 'セキュリティ対策として著者アーカイブを無効にします。',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'disable_update',
			__( '自動更新の無効化', '' ),
			function () {
				$args = array(
					'id'      => 'disablepports_general_auto_update',
					'default' => 0,
					'desc' => 'WordPress本体、プラグインの更新を停止し非表示にします。',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		/**
		 * 2 ダッシュボードウィジェット
		 */
		$this->add_section( 'dashboard', function () {
			echo 'ダッシュボードウィジェットに表示するコンテンツを入力してください。';
		} );

		$this->add_field(
			'contents',
			__( 'コンテンツ', '' ),
			function () {
				$contents           = ( get_option( 'ggsupports_options' ) ) ? get_option( 'ggsupports_options' ) : '';
				$dashboard_contents = ( isset( $contents['ggsupports_dashboard_contents'] ) ? $contents['ggsupports_dashboard_contents'] : '' );
				$editor_settings    = array(
					'wpautop'             => false,
					'media_buttons'       => true,
					'default_editor'      => '',
					'drag_drop_upload'    => true,
					'textarea_name'       => 'ggsupports_options[ggsupports_dashboard_contents]',
					'textarea_rows'       => 50,
					'tabindex'            => '',
					'tabfocus_elements'   => ':prev,:next',
					'editor_css'          => '',
					'editor_class'        => '',
					'teeny'               => false,
					'dfw'                 => true,
					'_content_editor_dfw' => false,
					'tinymce'             => true,
					'quicktags'           => true
				);
				wp_editor( $dashboard_contents, 'ggsupports_options_ggsupports_dashboard_contents', $editor_settings );
			},
			'dashboard'
		);

	}

	/**
	 * Setting APIを使用したオプションページの作成
	 * @return void
	 */
	public function add_admin_menu() {

		add_options_page( 'GrowGroup制作サポート', 'GrowGroup制作サポート', 'manage_options', $this->option_page_slug, array(
			$this,
			'option_page'
		) );

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
				wp_enqueue_script( 'ggs_dashboard_widget', plugins_url( 'assets/js/dashboard.js', __FILE__ ), array( 'jquery' ), false );
				break;
			case 'settings_page_ggsupports_options_page' :
				wp_enqueue_script( 'ggs_admin_scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array(
					'jquery',
					'jquery-ui-tabs',
					'jquery-ui-button',
					'jquery-ui-accordion'
				), false );
				wp_enqueue_style( 'jquery-ui-smoothness', plugins_url( 'assets/css/jquery-ui.css', __FILE__ ), false, null );
				break;
		}

	}

	/**
	 * SettingAPI add_settings_section のラッパー
	 *
	 * @param $name
	 * @param $title
	 */
	public function add_section( $name, $title ) {
		add_settings_section(
			$this->prefix . $name . '_section',
			$title,
			'',
			$this->option_page_slug
		);
	}

	public function add_field( $name, $title, $callback, $section, $desc = '' ) {

		add_settings_field(
			$this->prefix . $section . '_' . $name,
			'<h3><span class="dashicons dashicons-arrow-right-alt2"></span> ' . $title . '</h3>',
			$callback,
			$this->option_page_slug,
			$this->prefix . $section . '_section'
		);

	}

}