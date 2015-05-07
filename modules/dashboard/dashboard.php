<?php
/*
Plugin Name: Original Dashboard Widget
Description: Please input the content to be displayed on the dashboard widget.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\dashboard;

use WP_Assistant\inc\config;
use WP_Assistant\modules\module;

class dashboard extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
		add_action( 'admin_init', array( $this, 'add_settings' ), 10 );

		if ( config::get_option( 'dashboard_contents' ) ) {
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
			add_action( 'load-index.php', array( $this, 'hide_welcome_panel' ) );
		}
	}

	/**
	 * 管理画面の設定
	 * @return void
	 */
	public function add_settings() {

		$this->parent->settings->add_section(
			array(
				'id'        => 'dashboard',
				'desc'      => __( 'Please input the content to be displayed on the dashboard widget.', 'wp-assistant' ),
				'tabs_name' => __( 'Replace Welcome Panel', 'wp-assistant' )
			)
		)
	   ->add_field(
	       array(
	           'id'      => 'dashboard_contents',
	           'title'   => __( 'Dashboard Contents', 'wp-assistant' ),
	           'type'    => 'source',
	           'default' => '',
	           'section' => 'dashboard',
	           'desc'    => __( '<p>Please enter the content to be displayed on the dashboard.</p>', 'wp-assistant' ),
		       'options' => array(
					'lang' => 'php',
					'height' => '500px',
					'width' => '100%',
				)
	       )
	   );
	}

	/**
	 * ダッシュボードに登録
	 */
	public function add_dashboard_widgets() {
			wp_add_dashboard_widget( 'wpa_dashboard_widget', get_bloginfo( 'title' ), function () {
				?>
				<div id="wpadashboard" class="original-panel">
					<div class="original-panel-content">
						<?php
						echo stripslashes_deep( config::get_option( 'dashboard_contents' ) );
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
