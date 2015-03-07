<?php
/*
Plugin Name: Original Dashboard Widget
Description: Please input the content to be displayed on the dashboard widget.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\dashboard;

use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;
use WP_Assistant\modules\module;

class dashboard extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
		add_action( 'admin_init', array( $this, 'add_settings' ), 10 );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_action( 'load-index.php', array( $this, 'hide_welcome_panel' ) );
	}

	/**
	 * 管理画面の設定
	 * @return void
	 */
	public function add_settings() {

		$this->parent->settings->add_section(
			'dashboard',
			function () {
				echo __( 'Please input the content to be displayed on the dashboard widget.', 'wp-assistant' );
			},
			__( 'Replace Welcome Panel', 'wp-assistant' )
		)
		->add_field(
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
	   )
	   ->add_field(
		   'dashboard_contents',
		   '',
		   function () {
			   echo '<div class="acoordion"';
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
			   echo '</div>';
		   },
		   'dashboard',
		   ''
	   );
	}

	/**
	 * ダッシュボードに登録
	 */
	public function add_dashboard_widgets() {
		if ( config::get_option( 'dashboard_disp' ) ) {
			wp_add_dashboard_widget( 'wpa_dashboard_widget', get_bloginfo( 'title' ), function () {
				?>
				<div id="wpadashboard" class="original-panel">
					<div class="original-panel-content">
						<?php
						echo html_entity_decode( config::get_option( 'dashboard_contents' ) );
						?>
					</div>
				</div>
			<?php
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
}