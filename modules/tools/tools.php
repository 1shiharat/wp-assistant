<?php
/**
 * =====================================================
 * 設定のエクスポート・インポート
 * @package   WP_Assistant
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * @todo インポートの動作を追加
 * =====================================================
 */
namespace WP_Assistant\modules\tools;

use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;

class tools {

	private static $instance = null;

	public function __construct(){
		add_action( 'wpa_settings_fields_after', array( $this, 'add_settings' ), 10, 1 );
		add_action( 'wp_ajax_wpa_option_import', array( $this, 'option_import' ) );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;

	}
	/**
	 * フィールドを追加
	 *
	 * @param $admin WP_Assistant\module\admin\admin クラスのインスタンス
	 * @return void
	 */
	public function add_settings( $admin ) {

		$admin->add_section(
			'tools',
			function () {
				_e( 'Tools', 'wp-assistant' );
			},
			__( 'Tools', 'wp-assistant' )
		);

		$admin->add_field(
			'tools_export',
			__( 'Export Settings', 'wp-assistant' ),
			function () {
				?>
				<div>
					<p><?php _e( 'Download the set as a text file.', 'wp-assistant' ); ?></p>
					<div class="tools-option-export">
						<a href='data:text/plain;charset=UTF-8,<?php echo serialize( get_option( config::get( 'prefix' ).'options' ) ); ?>' id="option-export" class="button-secondary" download="<?php echo config::get( 'prefix' ) . date( 'Ymd' ) ?>.txt"><?php _e( 'Export', 'wp-assistant' ); ?></a>
					</div>
				</div>
			<?php
			},
			'tools',
			0
		);

		$admin->add_field(
			'tools_export_text',
			__( 'Settings Copy', 'wp-assistant' ) ,
			function () {
				?>
				<div>
					<p></p>
					<textarea id="" cols="30" rows="10"><?php echo serialize( config::get_option() ); ?></textarea>
				</div>
				<?php
			},
			'tools',
			0
		);

		$admin->add_field(
			'tools_import',
			__( 'Import Settings', 'wp-assistant' ),
			function () {
				$nonce = wp_create_nonce( __FILE__ );
				?>
				<div>
					<input id="tools_option_import_file" type="file" />
					<p>
						<input type="hidden" id="tools_option_import_nonce" name="tools_option_import_nonce" value="<?php echo $nonce ?>" />
						<button id="tools_option_import" class="button-secondary"><?php _e( 'Import', 'wp-assistant' ); ?></button>
						<input type="hidden" name="tools_option_import_data" id="tools_option_import_data" value="" />
						<span class="spinner"></span>
					</p>
				</div>
				<?php
			},
			'tools',
			0
		);
	}

	/**
	 * オプションをインポート
	 * @return bool
	 */
	public function option_import(){
		$nonce = esc_html( $_REQUEST['wp_import_nonce'] );
		$importdata = $_REQUEST['import_data'];

		if ( ! wp_verify_nonce( $nonce, __FILE__ )
		     || !$importdata ) {
			return false;
			exit();
		}

		$data = maybe_unserialize( stripslashes_deep( $importdata ) );
		if ( is_array( $data ) ){
			update_option( config::get( 'prefix' ). 'options', $data );
			echo 1;
			exit();
		}

	}
}