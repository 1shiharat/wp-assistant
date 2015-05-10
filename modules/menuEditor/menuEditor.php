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
	public function __construct() {

		$this->settings = parent::get_settings();

		if (  intval( config::get_option( 'modules_list_menuEditor' ) ) === 0 ){
			return false;
		}

		if ( $this->flag === true ) {
			return false;
		}
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'admin_print_scripts', array( $this, 'scripts' ), 10 );
		// 設定を css と js として出力する
		add_action( 'admin_print_scripts', array( $this, 'enhanced' ), 999 );
		$this->flag = true;

	}


	/**
	 * 現在の設定を js に渡す
	 */
	public function scripts() {
		$admin_menus = config::get_option( 'admin_menu' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		$selected_user   = config::get_option( 'admin_menu_user' );

		if ( ! $selected_user ){
			$selected_user = array();
		}

		$current_user_id = get_current_user_id();

		$dialog_context = array(
			'dialog' => array(
				'title'   => __( 'Are you sure you want to reset?', 'wp-assistant' ),
				'context' => __( 'It is recommended that you take a backup.', 'wp-assistant' )
			),
			'user' => array(
				'userflag' => in_array( $current_user_id, $selected_user ),
			)
		);
		wp_localize_script( config::get( 'prefix' ) . 'admin_scripts', 'wpa_ADMIN_MENU', array_merge( array( 'menus' => $admin_menus ), $dialog_context ) );
	}

	/**
	 * 設定をcssとjsとして出力
	 * @return void
	 */
	public function enhanced() {
		$admin_menus     = ( $a = config::get_option( 'admin_menu' ) ) ? $a : '';
		$selected_user   = config::get_option( 'admin_menu_user' );
		$current_user_id = get_current_user_id();
		$true_menu_data  = array();
		parse_str( $admin_menus, $menus );

		if ( is_array( $menus ) ) {
			foreach ( $menus as $menu_key => $menu ) {
				preg_match( '/\d*/', $menu_key, $menu_number );
				preg_match( '|[a-z]+\$*|', $menu_key, $menu_text );
				$true_menu_data[ $menu_number[0] ][ $menu_text[0] ] = $menu;
			}
		}

		if ( is_array( $true_menu_data ) && isset( $true_menu_data[0] ) && is_array( $true_menu_data[0] ) ) {
			echo '<style id="admin-menu-hide-css">#adminmenu li{ display: none; }</style>';
		}
	}

	/**
	 * 設定ページにフィールドを追加
	 * @return void
	 */
	public function add_settings() {

		$this->settings->add_section(
			array(
				'id'        => 'admin_menu',
				'title'     => __( 'Admin Menu', 'wp-assistant' ),
				'tabs_name' => __( 'Admin Menu Settings', 'wp-assistant' ),
			)
		)->add_field(
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
					?>
					<p style="margin-top: 20px">
					<button class="btn button-secondary" id="wpa_menu_user_reset"><?php _e( 'Reset', 'wp-assistant' ); ?></button>
					</p>
					<?php
				},
				'default' => '0',
			)
		)->add_field(
			array(
				'id'      => 'admin_menu',
				'title'   => __( 'Select Admin Menu', 'wp-assistant' ),
				'desc'    => __( 'Set display, non-display of the admin menu item every user.', 'wp-assistant' ),
				'section' => 'admin_menu',
				'type'    => function () {
					$checked_admin_menus = config::get_option( 'admin_menu' ); ?>

					<form id="wpa_admin_menu_form" name="wpa_admin_menu_form" action="get">
						<div id="wpa_admin_menus_list"></div>
					</form>
					<input type="hidden" id="admin_menu_hidden" value="<?php echo $checked_admin_menus; ?>" name="admin_menu"/>
					<script type="text/template" id="wpa_admin_menus_template">
						<div id="wpa_admin_menus">
							<% _.each( menus, function(menu,key) { %>
							<div class="menu-list-item" data-order="<%= menu.order %>">
								<input type="checkbox" class="admin_menu_edit adminmenu_hidden_check" name="wpa_supports_checkobox[<%= menu.id %>][disp]" value="1" <% if ( menu.disp === '0'){ %> checked="checked"<% };%> />
								<span class="menu-list-item-text"><%= menu.text %> </span>

								<div class="wpa wpa_hide_form">
									<div class="wpa_input-group hide">
										<label for="" class="wpa_label wpa_label-sm"><?php _e( 'Label', 'wp-assistant' ) ?></label>
										<input type="text" class="admin_menu_edit menu-list-item-text_input" data-menu-id="<%= menu.id %>" name="wpa_supports_checkobox[<%= menu.id %>][name]" value="<%= menu.text %>"/>
									</div>
									<div class="wpa_input-group hide">
										<label for="" class="wpa_label wpa_label-sm"><?php _e( 'Link', 'wp-assistant' ) ?></label>
										<input type="text" class="admin_menu_edit menu-list-item-link_input" data-menu-id="<%= menu.id %>" name="wpa_supports_checkobox[<%= menu.id %>][link]" value="<%= menu.link %>"/>
									</div>
									<div class="wpa_input-group hide">
										<label for="" class="wpa_label wpa_label-sm"><?php _e( 'Target', 'wp-assistant' ) ?></label>
										<input type="text" class="admin_menu_edit menu-list-item-target_input" data-menu-id="<%= menu.id %>" name="wpa_supports_checkobox[<%= menu.id %>][target]" value="<%= menu.target %>"/>
									</div>
								</div>
								<button class="menu-list-item-text-save" style="display: none">
									<span class="dashicons dashicons-yes"></span></button>
								<a href="#" class="menu-list-item-remove"><i class="dashicons dashicons-trash"></i></a>
							</div>
							<% } ) %>
						</div>
					</script>
					<p style="margin-top: 20px;">
						<button id="wpa_admin_menu_reset" class="button-primary"><?php _e( 'Reset', 'wp-assistant' ); ?></button>
						<button id="add_menu_list_item" class="button-secondary"><?php _e( 'Add New', 'wp-assistant' ) ?></button>

					</p>

					<script type="text/template" id="wpa_admin_menu_template">
						<div class="menu-list-item new_menu-list-item" data-order="<%= menu.order %>">
							<input type="checkbox" class="admin_menu_edit adminmenu_hidden_check" name="wpa_supports_checkobox[<%= menu.id %>][disp]" value="1" <% if ( menu.disp === '0'){ %> checked="checked"<% };%> />
							<span class="menu-list-item-text"><span style="display: inline-block; height: 10px;"></span></span>
							<div class="wpa wpa_hide_form">
								<div class="wpa_input-group hide">
									<label for="" class="wpa_label wpa_label-sm"><?php _e( 'Label', 'wp-assistant' ) ?></label>
									<input type="text" class="admin_menu_edit menu-list-item-text_input" data-menu-id="<%= menu.id %>" name="wpa_supports_checkobox[<%= menu.id %>][name]" value="<%= menu.text %>"/>
								</div>
								<div class="wpa_input-group hide">
									<label for="" class="wpa_label wpa_label-sm"><?php _e( 'Link', 'wp-assistant' ) ?></label>
									<input type="text" class="admin_menu_edit menu-list-item-link_input" data-menu-id="<%= menu.id %>" name="wpa_supports_checkobox[<%= menu.id %>][link]" value="<%= menu.link %>"/>
								</div>
								<div class="wpa_input-group hide">
									<label for="" class="wpa_label wpa_label-sm"><?php _e( 'Target', 'wp-assistant' ) ?></label>
									<input type="text" class="admin_menu_edit menu-list-item-target_input" data-menu-id="<%= menu.id %>" name="wpa_supports_checkobox[<%= menu.id %>][target]" value="<%= menu.target %>"/>
								</div>
							</div>
							<button class="menu-list-item-text-save" style="display: none">
								<span class="dashicons dashicons-yes"></span></button>
						</div>
					</script>

					<script type="text/template" id="wpa_admin_menu_dialog">
						<div id="admin_menu_dialog" title="<%= title %>">
							<p><%= context %></p>
						</div>
					</script>

				<?php
				},
			)
		);

	}
}
