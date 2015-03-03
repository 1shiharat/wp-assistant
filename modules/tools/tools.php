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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'ajax_ggs_option_import', array( $this, 'ggs_option_import' ) );
	}
	public function enqueue_scripts( $hook ){
		if ( 'toplevel_page_' . config::get( 'prefix' ) . 'options_page'  == $hook ){
			wp_enqueue_script( 'ggs_admin_scripts', config::get( 'plugin_url' ) . 'modules/tools/assets/js/option-import.js', array( 'jquery' ), false );
		}
	}

	public function ggs_option_import(){

	}


	/**
	 * フィールドを追加
	 *
	 * @param $admin
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
					<div class="option-export">
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
				?>
				<div>
					<input id="import_file" type="file" />
				</div>
				<?php
			},
			'tools',
			0
		);

	}
}