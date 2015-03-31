<?php
/*
Plugin Name: Admin Menu Editor
Description: Set display, non-display of the admin menu item every user.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\menuEditor;

use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;
use WP_Assistant\modules\module;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class menuEditor extends module {

	public $flag = false;
	/**
	 * 初期化
	 */
	public function __construct( $parent ) {
		if ( $this->flag === true ){
			return false;
		}
		$this->parent = $parent;

		add_action( 'admin_init', array( $this, 'add_settings' ) );

		add_action( 'admin_print_scripts', function () {
			$admin_menus = config::get_option( 'admin_menu' );
			wp_localize_script( config::get( 'prefix' ) . 'admin_scripts', 'wpa_ADMIN_MENU', array( 'menus' => $admin_menus ) );
		}, 10 );

		add_action( 'admin_menu', function () {
			$admin_menus = config::get_option( 'admin_menu' );
			if ( ! $admin_menus ) {
				return;
			}
			$admin_menu_array = explode( ',', $admin_menus );
			foreach ( $admin_menu_array as $admin_menu ) {
				if ( $admin_menu ) {
					remove_menu_page( $admin_menu );
				}
			}
		}, 10 );

		add_action( 'admin_print_scripts', function () {
			$admin_menus     = config::get_option( 'admin_menu' );
			$selected_user   = config::get_option( 'admin_menu_user' );
			$current_user_id = get_current_user_id();
			$true_menu_data = array();

			parse_str( $admin_menus, $menus );

			if ( is_array( $menus ) ){
				foreach( $menus as $menu_key => $menu ){
					preg_match('/\d*/', $menu_key, $menu_number );
					preg_match('|[a-z]+\$*|', $menu_key, $menu_text );
					$true_menu_data[$menu_number[0]][$menu_text[0]] = $menu;
				}
			}

			if ( is_array( $selected_user )
			     && in_array( $current_user_id, $selected_user )
			     && $true_menu_data
			) {
				echo '<style>';
				foreach ( $true_menu_data as $menu ) {
					if ( $menu['disp'] == 0 ) {
						echo '#' . $menu['id'] . '{ display: none !important}';
					}
					echo '#' . $menu['id'] . ' .wp-menu-name{ display: none}';
				}
				echo '</style>';
			}
			echo '<style>#adminmenu li .wp-menu-name{ display: none; }</style>';
			echo '<script type="text/javascript">
(function($){
	$(function(){
	';
			foreach ( $true_menu_data as $menu ) {?>
		$('#<?php echo esc_attr( $menu['id'] ); ?> ').find('.wp-menu-name').text("<?php echo $menu['title'] ?>").fadeIn(500);
					<?php
			}
			echo '});
})(jQuery);</script>';
		}, 999 );

		$this->flag = true;

	} // construct

	/**
	 * フィールドを追加
	 */
	public function add_settings() {

		$this->parent->settings->add_section(
			array(
				'id'        => 'admin_menu',
				'title'     => __( 'Admin Menu', 'wp-assistant' ),
				'tabs_name' => __( 'Admin Menu Settings', 'wp-assistant' ),
			)
		)
		->add_field(
			array(
				'id'      => 'admin_menu_user',
				'title'   => __( 'Select User', 'wp-assistant' ),
				'desc'    => __( 'Please select the account to apply the Admin menu change. <br /> * You can select more than one by selecting while holding down the  shift key.', 'wp-assistant' ),
				'section' => 'admin_menu',
				'type'    => function () {
					$selected = config::get_option( 'admin_menu_user' );
					helper::dropdown_users( array(
						'name'     => 'admin_menu_user[]',
						'id'       => 'admin_menu_user',
						'selected' => $selected
					) );
				},
				'default' => '0',
			)
		)
		->add_field(
			array(
				'id'      => 'admin_menu',
				'title'   => __( 'Select Admin Menu', 'wp-assistant' ),
				'desc'    => __( 'Set display, non-display of the admin menu item every user.', 'wp-assistant' ),
				'section' => 'admin_menu',
				'type'    => function () {
					$checked_admin_menus = config::get_option( 'admin_menu' ); ?>
					<form id="wpa_admin_menu_form" name="wpa_admin_menu_form" action="get">
						<div id="wpa_admin_menus"></div>
					</form>
					<input type="hidden" id="admin_menu_hidden" value="<?php echo $checked_admin_menus; ?>" name="admin_menu"/>
					<?php
				},
			)
		);

	}
}
