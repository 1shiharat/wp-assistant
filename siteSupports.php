<?php
/*
Plugin Name: GrowGroup - サイト制作サポート
Plugin URI: http://grow-group.jp/
Description: Webサイト制作のお助けプラグイン
Author: 1shiharaT
Version: 0.0.3
Author URI: http://grow-group.jp/
*/

namespace siteSupports;

use siteSupports\modules\aceEditor;
use siteSupports\modules\admin;
use siteSupports\modules\breadcrumb;
use siteSupports\modules\cf7AjaxZip;
use siteSupports\modules\cleanup;
use siteSupports\modules\menuEditor;
use siteSupports\modules\optimize;
use siteSupports\modules\tools;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require 'autoload.php';
require 'config.php';
$GLOBALS['siteSupports'] = \siteSupports\siteSupports::get_instance();

register_activation_hook( __FILE__, \siteSupports\siteSupports::activate() );

/**
 * Class GGSupports
 * プラグインのメインクラス
 */
class siteSupports {

	private static $instance = null;

	/**
	 * インスタンスを取得
	 * @return GGSupports|null クラスのインスタンス
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * 初期化
	 *
	 * 各モジュールをアクションフックに登録
	 * @return void
	 */
	private function __construct() {
		new \siteSupports\autoload();

		// キャッシュをセット
		static::set_cache();
		// wp_headやwp_footerなどから余計な記述を削除
		add_action( 'plugins_loaded', array( new cleanup\cleanup(), '__construct' ), 0 );
		add_action( 'admin_init', array( new admin\admin(), '__construct' ), 10 );
		add_action( 'admin_init', array( new aceEditor\aceEditor(), '__construct' ), 10 );
		add_action( 'admin_init', array( new menuEditor\menuEditor(), '__construct' ), 10 );
		add_action( 'admin_init', array( new optimize\optimize(), '__construct' ), 10 );
		add_action( 'admin_init', array( new tools\tools(), '__construct' ), 10 );
		add_action( 'init', array( new cf7AjaxZip\cf7AjaxZip(), 'cf7AjaxZip' ), 10 );
		add_action( 'init', array( new breadcrumb\breadcrumb(), '__construct' ), 10 );
	}

	/**
	 * キャッシュをセット
	 */
	private static function set_cache() {
		// キャッシュをセット
		config::set( 'prefix', 'ggs_' );
		config::set( 'plugin_dir', plugin_dir_path( __FILE__ ) );
		config::set( 'plugin_url', plugins_url( '/', __FILE__ ) );
		config::set( 'options', get_option( config::get( 'prefix' ) . 'options' ) );
		if ( get_option( config::get( 'prefix' ) . 'install' ) ) {
			config::set( 'install', true );
		}
	}

	/**
	 * プラグイン有効化時のアクション
	 */
	public static function activate() {
		static::set_cache();
		update_option( config::get( 'prefix' ) . 'install', true );
	}

}
