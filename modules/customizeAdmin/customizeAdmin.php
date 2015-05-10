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
	public function __construct() {
		$this->settings = parent::get_settings();
		add_action( 'admin_init', array( $this, 'add_settings' ), 10 );
		add_action( 'login_enqueue_scripts', array( $this, 'change_login_panel' ) );
		add_filter( 'admin_footer_text', array( $this, 'change_footer_text' ), 10, 1 );
	}

	/**
	 * 管理画面の設定
	 * @return void
	 */
	public function add_settings() {

		$this->settings->add_section(
			array(
				'id'        => 'customize_admin',
				'desc'      => __( 'Change the logo management screen and text settings', 'wp-assistant' ),
				'tabs_name' => __( 'Customize Admin', 'wp-assistant' )
			)
		)
		->add_field(
			array(
				'id'      => 'login_panel_logo',
				'title'   => __( 'Login panel', 'wp-assistant' ),
				'type'    => 'media',
				'default' => '',
				'section' => 'customize_admin',
				'desc'    => __( 'Please upload a logo in the login screen.', 'wp-assistant' )
			)
		)
		->add_field(
			array(
				'id'      => 'login_panel_background',
				'title'   => __( 'Login panel background', 'wp-assistant' ),
				'type'    => 'media',
				'default' => '',
				'section' => 'customize_admin',
				'desc'    => __( 'Please upload the login screen background image. Will automatically be stretched. As recommended large size images.', 'wp-assistant' )
			)
		)
		->add_field(
			array(
				'id'      => 'admin_footer_text',
				'title'   => __( 'Admin footer text', 'wp-assistant' ),
				'type'    => 'text',
				'default' => '',
				'section' => 'customize_admin',
				'desc'    => __( 'change to footer text', 'wp-assistant' )
			)
		);

	}

	/**
	 * アタッチメントIDを取得
	 * @param $url
	 *
	 * @return int
	 */
	public function get_attachment_id( $url ) {
		global $wpdb;
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s";
		preg_match('/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches);
		$post_name = $matches[1];
		return (int)$wpdb->get_var($wpdb->prepare($sql, $post_name));
	}

	/**
	 * ログイン画面を変更
	 *
	 */
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
					background-color: rgba(255,255,255,0.7);
					background-position: center center;
				}
			</style>
		<?php
		}
	}

	public function change_footer_text( $text ){
		$footer_text = config::get_option( 'admin_footer_text' );
		if ( $footer_text ){
          return wp_kses_post( $footer_text );
		}
		return $text;
	}

}
