<?php
namespace siteSupports\modules\menuEditor;

use siteSupports\config;
use siteSupports\inc\helper;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class menuEditor{

	/**
	 * 初期化
	 */
	public function __construct(){

		add_action( 'admin_print_scripts', function(){
			wp_enqueue_script( 'ggs_admin_menu',  config::get( 'plugin_url' ) . 'modules/menuEditor/assets/js/admin-menu.js', null, null );
			$admin_menus = config::get_option( 'admin_menu' );
			wp_localize_script( 'ggs_admin_menu', 'GGS_ADMIN_MENU', array( 'menus' => $admin_menus ) );
		}, 9999 );

		add_action( 'admin_menu', function(){
			$admin_menus = config::get_option( 'admin_menu' );
			if ( ! $admin_menus ) return;
			$admin_menu_array =  explode( ',', $admin_menus );
			foreach ( $admin_menu_array as $admin_menu ){
				if ( $admin_menu ) {
					remove_menu_page( $admin_menu );
				}
			}
		}, 10 );

		add_action( 'admin_print_scripts', function(){
			$admin_menus = config::get_option( 'admin_menu' );
			$selected_user = config::get_option( 'admin_menu_user' );
			$current_user_id = get_current_user_id();
			if ( ! is_array( $selected_user )
				|| ! in_array( $current_user_id, $selected_user )
				|| ! $admin_menus ){
				return false;
			}
			$menus_array = explode( ',', $admin_menus );
			echo '<style>';
			foreach( $menus_array as $menu ){
				if ( $menu ){
					echo "#$menu{ display: none !important}";
				}
			}
			echo '</style>';
		}, 999 );

	} // construct

}