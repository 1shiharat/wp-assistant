<?php
/**
 * =====================================================
 * 設定のエクスポート・インポート
 * @package   siteSupports
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * @todo インポートの動作を追加
 * =====================================================
 */
namespace siteSupports\modules\tools;

use siteSupports\config;
use siteSupports\inc\helper;

class tools {

	public function __construct(){
		add_action( 'ggs_settings_fields_after', array( $this, 'add_settings' ), 10, 1 );
		add_action( 'wp_ajax_ggs_option_import', array( $this, 'option_import' ) );
	}

	/**
	 * フィールドを追加
	 *
	 * @param $admin siteSupports\module\admin\admin クラスのインスタンス
	 * @return void
	 */
	public function add_settings( $admin ) {

		$admin->add_section(
			'tools',
			function () {
				echo 'ツール';
			},
			__( 'ツール', 'ggsupports' )
		);

		$admin->add_field(
			'tools_export',
			__( '設定のエクスポート', 'ggsupports' ),
			function () {
				?>
				<div>
					<p><?php _e( '設定をテキストファイルとしてダウンロード', 'ggsupports' ); ?></p>
					<div class="tools-option-export">
						<a href='data:text/plain;charset=UTF-8,<?php echo serialize( config::get_option() ); ?>' id="option-export" class="button-secondary" download="<?php echo config::get( 'prefix' ) . date( 'Ymd' ) ?>.txt"><?php _e( '設定をエクスポート', 'ggsupports' ); ?></a>
					</div>
				</div>
			<?php
			},
			'tools',
			0
		);

		$admin->add_field(
			'tools_export_text',
			__( '設定をコピー', 'ggsupports' ) ,
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
			__( '設定をインポート', 'ggsupports' ),
			function () {
				$nonce = wp_create_nonce( __FILE__ );
				?>
				<div>
					<input id="tools_option_import_file" type="file" />
					<p>
						<input type="hidden" id="tools_option_import_nonce" name="tools_option_import_nonce" value="<?php echo $nonce ?>" />
						<button id="tools_option_import" class="button-secondary">設定をインポート</button>
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