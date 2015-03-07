<?php
/*
Plugin Name: Admin Menu Editor
Description: Set display, non-display of the admin menu item every user.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\menuEditor;

use WP_Assistant\modules\module;
use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class menuEditor extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ){
		$this->parent = $parent;

		add_action('admin_init', array( $this, 'add_settings' ) );

		add_action( 'admin_print_scripts', function(){
			$admin_menus = config::get_option( 'admin_menu' );
			wp_localize_script( config::get( 'prefix' ) . 'admin_scripts', 'wpa_ADMIN_MENU', array( 'menus' => $admin_menus ) );
		}, 10 );

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


	/**
	 * フィールドを追加
	 */
	public function add_settings(){

		$this->parent->settings->add_section(
			'admin_menu', function () {
				_e( 'Admin Menu', 'wp-assistant' );
			},
			__( 'Admin Menu Settings', 'wp-assistant' )
		)
		->add_field(
			'admin_menu_user',
			__( 'Select User', 'wp-assistant' ),
			function () {
				?>
				<div>
				<?php
				_e( 'Please select the account to apply the Admin menu change. <br /> * You can select more than one by selecting while holding down the  shift key.', 'wp-assistant' );
				echo '<br />';
				$selected = config::get_option( 'admin_menu_user' );
				helper::dropdown_users( array(
					'name'     => 'admin_menu_user[]',
					'id'       => 'admin_menu_user',
					'selected' => $selected
				) ); ?>
				</div>
				</div>

			<?php
			},
			'admin_menu',
			''
		)
		->add_field(
			'admin_menu',
			__( 'Select Admin Menu', 'wp-assistant' ),
			function () { ?>
				<div>
				<?php
				$checked_admin_menus = config::get_option( 'admin_menu' ); ?>
				<p><?php _e( 'Please refer to the select the management menu you want to hide.', 'wp-assistant' ); ?></p>
				<div id="wpa_admin_menus"></div>

				<input type="hidden" id="admin_menu_hidden" value="<?php echo $checked_admin_menus; ?>" name="admin_menu"/>
				</div>
				</div>

			<?php
			},
			'admin_menu',
			''
		);
	}
}