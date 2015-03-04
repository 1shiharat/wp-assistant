<?php
/**
 * =====================================================
 * 管理画面サイドメニューを管理
 * @package   WP_Assistant
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace WP_Assistant\modules\menuEditor;

use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class menuEditor{

	private static $instance = null;

	/**
	 * 初期化
	 */
	public function __construct(){

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

		add_action( 'wpa_settings_fields_after', array( $this, 'add_fields' ), 10, 1 );

	} // construct

	public static function get_instance() {

		if ( null == static::$instance ) {
			static::$instance = new static;
		}
		return self::$instance;

	}

	/**
	 * フィールドを追加
	 * @param $admin
	 */
	public function add_fields( $admin ){

		$admin->add_section( 'admin_menu', function () {
			_e( 'Admin Menu', 'wp-assistant' );
		}, __( 'Admin Menu Settings', 'wp-assistant' ) );

		$admin->add_field(
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
			<?php
			},
			'admin_menu',
			''
		);

		$admin->add_field(
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
			<?php
			},
			'admin_menu',
			''
		);
	}
}