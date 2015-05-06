<?php
/*
Plugin Name: WP Assistant
Plugin URI: http://grow-group.jp/
Description: This plugin to provide a convenient function when build WordPress site.
Author: 1shiharaT
Version: 0.2.4
Author URI: http://grow-group.jp/
Text Domain: wp-assistant
Domain Path: /languages/
*/
/**
 * =====================================================
 * @package   WP_Assistant
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace WP_Assistant;

use WP_Assistant\inc\config;
use WP_Assistant\inc\autoload;

use WP_Assistant\modules\module;
use WP_Assistant\modules\register;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require 'inc/config.php';
require 'inc/autoload.php';

$GLOBALS['WP_Assistant'] = new WP_Assistant();

register_activation_hook( __FILE__, array( $GLOBALS['WP_Assistant'], 'activate' ) );

/**
 * Class wpaupports
 * プラグインのメインクラス
 */
class WP_Assistant {


	/**
	 * 初期化
	 * 各モジュールをアクションフックに登録
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		new \WP_Assistant\inc\autoload();

		// キャッシュをセット
		static::set_cache();

		add_action( 'plugins_loaded', array( $this, 'action' ) );
	}

	public function action(){
		$modules = new module();
	}

	/**
	 * 翻訳ファイルを登録
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-assistant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * キャッシュをセット
	 */
	private static function set_cache() {
		config::set( 'version', static::get_version() );
		config::set( 'prefix', 'wpa_' );
		config::set( 'plugin_dir', plugin_dir_path( __FILE__ ) );
		config::set( 'plugin_url', plugins_url( '/', __FILE__ ) );
		config::set( 'options', get_option( config::get( 'prefix' ) . 'options' ) );
		if ( get_option( config::get( 'prefix' ) . 'install' ) ) {
			config::set( 'install', true );
		}
	}

	/**
	 * プラグイン有効化時のアクション
	 * @return bool
	 */
	public static function activate() {
		static::set_cache();
		return update_option( config::get( 'prefix' ) . 'install', true );
	}

	/**
	 * プラグインのバージョン情報を取得
	 * @return string
	 */
	public static function get_version() {
		$filedata = get_file_data( __FILE__, array( 'version' => 'version' ) );
		return $filedata['version'];
	}

	public static function get_module_name() {
		$filedata = get_file_data( __FILE__, array( 'Name' => 'Plugin Name' ) );
		return $filedata['name'];
	}

	public static function get_module_desc() {
		$filedata = get_file_data( __FILE__, array( 'Description' => 'Description', ) );
		return $filedata['description'];
	}

}
