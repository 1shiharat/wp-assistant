<?php
/**
 * =====================================================
 * サイト制作サポートプラグイン
 * @package   WP_Assistant
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
/*
Plugin Name: WP Assistant
Plugin URI: http://grow-group.jp/
Description: WordPress assistant plugin.
Author: 1shiharaT
Version: 0.1.2
Author URI: http://grow-group.jp/
Text Domain: wp-assistant
Domain Path: /languages/
*/

namespace WP_Assistant;

use WP_Assistant\inc\config;
use WP_Assistant\inc\autoload;

use WP_Assistant\modules\cleanup\cleanup;
use WP_Assistant\modules\aceEditor\aceEditor;
use WP_Assistant\modules\admin\admin;
use WP_Assistant\modules\breadcrumb\breadcrumb;
use WP_Assistant\modules\cf7AjaxZip\cf7AjaxZip;
use WP_Assistant\modules\menuEditor\menuEditor;
use WP_Assistant\modules\optimize\optimize;
use WP_Assistant\modules\tools\tools;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require 'inc/config.php';
require 'inc/autoload.php';

$GLOBALS['WP_Assistant'] = new WP_Assistant();

register_activation_hook( __FILE__, array( new WP_Assistant, 'activate' ) );

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

		// 各モジュールを登録
		cleanup::get_instance();
		admin::get_instance();
		aceEditor::get_instance();
		menuEditor::get_instance();
		optimize::get_instance();
		tools::get_instance();
		cf7AjaxZip::get_instance();
		breadcrumb::get_instance();
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
}
