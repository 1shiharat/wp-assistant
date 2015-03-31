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

use WP_Assistant\inc\helper;
use WP_Assistant\modules\module;

class activation extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
		add_action( 'admin_init', array( $this, 'add_settings' ), 10 );
	}

	/**
	 * 管理画面の設定
	 * @return void
	 */
	public function add_settings() {
		$self = $this;
		$this->parent->settings->add_section(
			array(
				'id'    => 'modules',
				'title' => __( 'Modules Activation', 'wp-assistant' ),
				'desc'  => '',
				'tabs_name' => __( 'Modules Activation', 'wp-assistant' ),
			)
		);
		$this->select_modules();
	}

	/**
	 * モジュールの有効化一覧を取得
	 */
	public function select_modules() {
		$modules = $this->parent->get_modules();
		foreach ( $modules as $module => $module_info ) {

			if ( $module_info['name'] && $module_info['activation'] === 1 ) {

				$this->parent->settings->add_field(
					array(
						'id' => 'modules_list_' . $module,
						'title' => $module_info['name'],
						'type' => 'radiobox',
						'section' =>'modules',
						'default' => $module_info['default'],
						'desc' => esc_html( $module_info['desc'] ),
					)
				);
			}
		}
	}


}
