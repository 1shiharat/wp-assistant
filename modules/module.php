<?php
/**
 * 各モジュールの親クラス
 */
namespace WP_Assistant\modules;

use \WP_Assistant\inc\config;
use \WP_Assistant\inc\helper;
use \WP_Assistant\inc\settings;

class module{

	/** @var null  */
	public $parent = null;

	/** @var null|object  */
	public $settings = null;

	/** @var null モジュールキーの配列 */
	public $modules = null;

	public $modules_instance = null;

	/**
	 * Constructer
	 * 各モジュールのインスタンスを登録
	 */
	public function __construct(){

		$this->settings = $this->get_settings();
		$this->set_modules();

		$this->modules_instance = $this->register_modules( $this->modules );

	}

	function get_settings(){
		return settings::get_instance();
	}

	/**
	 * 有効なモジュールをセット
	 * cacheモジュールはまだ未完成のため無効化
	 */
	public function set_modules(){
		if ( $this->modules == null ){
			$modules_list = $this->get_modules();

			foreach( $modules_list as $key => $module ){
				if ( 'activation' == $key ){
					continue;
				}

				$enhanced = config::get_option( 'modules_list_' . $key, $module['default'] );

				if ( $enhanced == "0" && $module['activation'] == 0  ){
					unset( $modules_list[$key] );
				}
			}

			/** @var modules 有効なモジュールの配列 */
			$this->modules = apply_filters( 'wp_assistant_modules', $modules_list );
		}
	}

	/**
	 * モジュールを取得
	 * @return null
	 */
	public function get_modules(){
		return array(
			'activation' => array(
				'name' => '',
				'desc' => '',
			),
			'admin' => array(
				'name' => __( 'General', 'wp-assistant' ),
				'desc' => __( 'General setting for this site.', 'wp-assistant' ),
				'default' => 1,
				'activation' => 0,
			),
			'dashboard' => array(
				'name' => __( 'Original Dashboard Widget', 'wp-assistant' ),
				'desc' => __( 'Original dashboard widget module.', 'wp-assistant' ),
				'default' => 1,
				'activation' => 0,
			),
			'breadcrumb' => array(
				'name' => __( 'Breadcrumbs', 'wp-assistant' ),
				'desc' => __( 'breadcrumbs for this site.', 'wp-assistant' ),
				'default' => 1,
				'activation' => 0,
			),
			'cf7AjaxZip' => array(
				'name' => __( 'AjaxZip3 for Contact Form 7', 'wp-assistant' ),
				'desc' => __( '"zip", a name of a prefecture can implement automatic input to a zip code by using "address" for "pref", the address.', 'wp-assistant' ),
				'default' => 0,
				'activation' => 1,
			),
			'menuEditor' => array(
				'name' => __( 'Admin Menu Editor', 'wp-assistant' ),
				'desc' => __( 'Set display, non-display of the admin menu item every user.', 'wp-assistant' ),
				'default' => 1,
				'activation' => 1,
			),
			'customizeAdmin' => array(
				'name' => __( 'Customize Admin', 'wp-assistant' ),
				'desc' => __( 'Change the logo management screen and text settings', 'wp-assistant' ),
				'default' => 1,
				'activation' => 0,
			),
			'optimize' => array(
				'name' => __( 'Database Optimization', 'wp-assistant' ),
				'desc' => __( 'Database Optimization module.', 'wp-assistant' ),
				'default' => 1,
				'activation' => 0,
			),
			'tools' => array(
				'name' => __( 'Tools', 'wp-assistant' ),
				'desc' => __( 'Export & import of this plugin setting.', 'wp-assistant' ),
				'default' => 1,
				'activation' => 0,
			),
			'aceEditor' => array(
				'name' => __( 'Ace Editor', 'wp-assistant' ),
				'desc' => __( 'Introduced Ace in file editor.', 'wp-assistant' ),
				'default' => 1,
				'activation' => 1,
			),

		);
	}

	/**
	 * モジュールの登録
	 * * 各モジュールのインスタンスを作成
	 * @param $modules
	 *
	 * @return array
	 */
	public function register_modules( $modules ){
		if ( is_array( $modules ) && $modules ){
			foreach ( $modules as $module => $module_info ){
				$module_name = 'WP_Assistant\modules\\' . $module . '\\' . $module;
				$modules_instance[$module] = new $module_name( $this );
			}
		}

		return $modules_instance;
	}

	public function get_module_file_path( $module_name ){
		return __DIR__ . '/' . $module_name . '/' . $module_name . '.php';
	}

	/**
	 * モジュールのファイルヘッダーを取得
	 * @param $module_name
	 *
	 * @return array
	 */
	public function get_module_info( $module_name ){
		$modules = $this->get_modules();
		return $modules[$module_name];
	}

	/**
	 * プラグインのバージョン情報を取得
	 * @return string
	 */
	public static function get_version( $module_path ) {
		$filedata = get_file_data( $module_path, array( 'version' => 'version' ) );
		return $filedata['version'];
	}

	/**
	 * モジュール名を取得
	 *
	 * @param $module_path
	 *
	 * @return mixed
	 */
	public static function get_module_name( $module_path ) {
		$filedata = get_file_data( $module_path, array( 'name' => 'Plugin Name' ) );
		return $filedata['name'];
	}

	/**
	 * モジュールの説明文
	 *
	 * @param $module_path
	 *
	 * @return mixed
	 */
	public static function get_module_desc( $module_path ) {
		$filedata = get_file_data( $module_path, array( 'description' => 'Description', ) );
		return $filedata['description'];
	}


}
