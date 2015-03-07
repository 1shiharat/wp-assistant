<?php
/**
 * =====================================================
 * 管理画面設定ページを構築
 * @package   WP_Assistant
 * @author    Grow Group
 * @license   gpl v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace WP_Assistant\modules\activation;

use \WP_Assistant\modules\module;
use \WP_Assistant\inc\config;
use \WP_Assistant\inc\helper;

class activation extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ){
		$this->parent = $parent;
		add_action('admin_init', array( $this, 'add_settings' ), 10 );
	}

	/**
	 * 管理画面の設定
	 * @return void
	 */
	public function add_settings() {
		$this->parent->settings->add_section(
			'modules', '', __( 'Modules Activation', 'wp-assistant' )
		);
		$this->select_modules();
	}

	/**
	 * モジュールの有効化一覧を取得
	 */
	public function select_modules(){
		$modules = $this->parent->get_modules();
		foreach ( $modules as $module => $module_info){

			if ( $module_info['name'] ){
				$module_setting = function() use ( $module_info, $module ){ ?>
					<div class="module-block">
						<?php
						helper::radiobox(
							array(
								'id'      => 'modules_list_' . $module,
								'desc'    => esc_html( $module_info['desc'] ),
								'default' => $module_info['default'],
							)
						); ?>
					</div>
					<?php
				};

				$this->parent->settings->add_field(
					'modules_list_' . $module,
					$module_info['name'],
					$module_setting,
					'modules',
					$module_info['default']
				);
			}
		}
	}


}