<?php
/*
Plugin Name: Tools
Description: Export & import of this plugin setting.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\tools;

use WP_Assistant\inc\config;
use WP_Assistant\modules\module;
use WP_Assistant\modules\settings;

class tools extends module {


	public function __construct() {
		$this->settings = parent::get_settings();
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'wp_ajax_wpa_option_import', array( $this, 'option_import' ) );
	}

	/**
	 * フィールドを追加
	 *
	 * @return void
	 */
	public function add_settings() {
		$this->settings->add_section(
			array(
				'id'        => 'tools',
				'title'     => __( 'Tools', 'wp-assistant' ),
				'tabs_name' => __( 'Tools', 'wp-assistant' ),
			)
		)->add_field(
			array(
				'id'      => 'tools_export',
				'title'   => __( 'Export Settings', 'wp-assistant' ),
				'type'    => function () {
					?>
					<div class="tools-option-export">
						<a href='data:text/plain;charset=UTF-8,<?php echo serialize( get_option( config::get( 'prefix' ) . 'options' ) ); ?>' id="option-export" class="button-secondary" download="<?php echo config::get( 'prefix' ) . date( 'Ymd' ) ?>.txt"><?php _e( 'Export', 'wp-assistant' ); ?></a>
					</div>
				<?php
				},
				'desc'    => __( 'Download the set as a text file.', 'wp-assistant' ),
				'section' => 'tools',
			)
		)->add_field(
			array(
				'id'      => 'tools_export_text',
				'title'   => __( 'Settings Copy', 'wp-assistant' ),
				'type'    => function () {
					?>
					<div>
						<textarea id="" cols="40" rows="10"><?php echo htmlentities( serialize( config::get_option() ) ); ?></textarea>
					</div>
				<?php
				},
				'section' => 'tools',
				'default' => 0
			)
		)->add_field(
			array(
				'id'      => 'tools_import',
				'title'   => __( 'Import Settings', 'wp-assistant' ),
				'type'    => function () {
					$nonce = wp_create_nonce( __FILE__ );
					?>
					<div>
						<input id="tools_option_import_file" type="file"/>
						<p>or</p>
						<textarea cols="40" rows="10" name="import_textarea" id="import_textarea"></textarea>
						<p>
							<input type="hidden" id="tools_option_import_nonce" name="tools_option_import_nonce" value="<?php echo $nonce ?>"/>
							<button id="tools_option_import" class="button-secondary"><?php _e( 'Import', 'wp-assistant' ); ?></button>
							<input type="hidden" name="tools_option_import_data" id="tools_option_import_data" value=""/>
							<span class="spinner"></span>
						</p>
					</div>
				<?php
				},
				'section' => 'tools',
				'default' => 0
			)
		);
	}

	/**
	 * オプションをインポート
	 * @return bool
	 */
	public function option_import() {
		$nonce      = esc_html( $_REQUEST['wp_import_nonce'] );
		$importdata = $_REQUEST['import_data'];

		if ( ! wp_verify_nonce( $nonce, __FILE__ )
		     || ! $importdata
		) {
			return false;
			exit();
		}

		$data = maybe_unserialize( stripslashes_deep( $importdata ) );
		if ( is_array( $data ) ) {
			update_option( config::get( 'prefix' ) . 'options', $data );
			echo 1;
			exit();
		}

	}
}
