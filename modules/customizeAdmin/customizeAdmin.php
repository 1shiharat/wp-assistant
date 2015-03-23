<?php
/*
Plugin Name: AjaxZip3 for Contact Form 7
Description: 郵便番号に"zip"、県名に"pref"、住所に"address"を使用することで自動入力が実装可能です。
Text Domain: wp-assistant
*/
namespace WP_Assistant\modules\customizeAdmin;

use WP_Assistant\modules\module;
use WP_Assistant\inc\config;

class customizeAdmin extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
		add_action( 'admin_init', array( $this, 'add_settings' ), 10 );
		add_action( 'login_enqueue_scripts', array( $this, 'change_login_panel' ) );
	}

	/**
	 * 管理画面の設定
	 * @return void
	 */
	public function add_settings() {

		$this->parent->settings->add_section(
			array(
				'id'        => 'customize_admin',
				'desc'      => __( 'Change the logo management screen and text settings', 'wp-assistant' ),
				'tabs_name' => __( 'Customize Admin', 'wp-assistant' )
			)
		)
		->add_field(
			array(
				'id'      => 'favicon',
				'title'   => __( 'Site Favicon', 'wp-assistant' ),
				'type'    => 'media',
				'default' => '',
				'section' => 'customize_admin',
				'desc'    => __( '<p>Please upload the favicon.</p>', 'wp-assistant' )
			)
		)
		->add_field(
			array(
				'id'      => 'login_panel_logo',
				'title'   => __( 'Login panel', 'wp-assistant' ),
				'type'    => 'media',
				'default' => '',
				'section' => 'customize_admin',
				'desc'    => __( '<p>Please upload a logo in the login screen.</p>', 'wp-assistant' )
			)
		)
		->add_field(
			array(
				'id'      => 'login_panel_background',
				'title'   => __( 'Login panel background', 'wp-assistant' ),
				'type'    => 'media',
				'default' => '',
				'section' => 'customize_admin',
				'desc'    => __( '<p>Please upload the favicon.</p>', 'wp-assistant' )
			)
		);
	}

	public function get_attachment_id( $url ) {
		global $wpdb;
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s";
		preg_match('/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches);
		$post_name = $matches[1];
		return (int)$wpdb->get_var($wpdb->prepare($sql, $post_name));
	}

	public function change_login_panel(){
		$logo_url = config::get_option( 'login_panel_logo' );
		$logo_bg_url = config::get_option( 'login_panel_background' );
		if ( $logo_url ){
			$logo_img_id = $this->get_attachment_id( $logo_url );
			$logo_img_array = wp_get_attachment_image_src( $logo_img_id, 'full', false );?>
			<style>
			.login #login h1 a {
				width: <?php echo $logo_img_array[1]; ?>px;
				min-height: <?php echo $logo_img_array[2]; ?>px;
				background: url(<?php echo esc_url( $logo_url ); ?>) no-repeat 0 0;
				background-size: 100% auto;
				background-position: center center;
			}
			</style>
			<?php
		}
		if ( $logo_bg_url ){
			$logo_bg_img_id = $this->get_attachment_id( $logo_bg_url );
			$logo_bg_img_array = wp_get_attachment_image_src( $logo_bg_img_id, 'full', false );?>
			<style>
				.login{
					background: url(<?php echo esc_url( $logo_bg_url ); ?>) no-repeat 0 0;
					background-size: auto 100%;
					background: rgba(255,255,255,0.7);
					background-position: center center;
				}
			</style>
		<?php
		}
	}

}