<?php
/*
Plugin Name: GrowGroup - サイト制作サポート
Plugin URI: http://grow-group.jp/
Description: Webサイト制作のお助けプラグイン
Author: 1shiharaT
Version: 1.0.0
Author URI: http://grow-group.jp/
*/

namespace siteSupports;

use siteSupports\modules\admin;
use siteSupports\modules\cleanup;
use siteSupports\modules\cf7AjaxZip3;
use siteSupports\modules\aceEditor;
use siteSupports\modules\menuEditor;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require 'autoload.php';
add_action( 'plugins_loaded', array( 'siteSupports\siteSupports', 'get_instance' ) );

class config{

	public static $prefix = 'ggs';

	public static $version = '1.0.0';

	public static $plugin_url = '';

	public static $plugin_path = '';

	/**
	 * キャッシュを取得
	 * @param $key
	 * @param $data
	 */
	public static function set( $key, $data ){
		wp_cache_set( $key, $data, static::$prefix . '_options' );
	}

	/**
	 * キャッシュから値を取得
	 * @return string
	 */
	public static function get( $key ){
		return wp_cache_get( $key, static::$prefix . '_options' );
	}

	/**
	 * キャッシュから値を削除
	 * @return string
	 */
	public static function delete( $key ){
		return wp_cache_delete( $key, static::$prefix . '_options' );
	}

	/**
	 * オプションを取得
	 */
	public static function get_options( $option_key = '' ){
		$options = get_option( 'ggsupports_options' );
		if ( $option_key ){
			if ( isset( $options[$option_key] )  ){
				return $options[$option_key];
			}
		} else {
			return $options;
		}
		return false;
	}
}


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
	 * コンストラクタ
	 */
	private function __construct() {
		// class のオートロード
		new \siteSupports\autoload();

		config::set( 'prefix', 'ggs_' );
		config::set( 'plugin_dir', plugin_dir_path( __FILE__ ) );
		config::set( 'plugin_url', plugins_url( '/', __FILE__ ) );
		config::set( 'install', true );

		// wp_headやwp_footerなどから余計な記述を削除
		add_action( 'init', array( new cleanup\cleanup(), '__construct' ), 0 );
		add_action( 'init', array( new admin\admin(), '__construct' ), 10 );
		add_action( 'init', array( new aceEditor\aceEditor(), '__construct' ), 10 );
		add_action( 'init', array( new menuEditor\menuEditor(), '__construct' ), 10 );

		// CF7拡張
		add_action( 'wp', array( 'cf7AjaxZip', 'cf7AjaxZip' ), 10 );
	}

	public function activate(){
		$options = get_option( 'ggsupports_options' );
		// オプションがない場合、初期設定
		if ( ! $options ){
			update_option( 'ggsupports_install', true );
		}

	}
}
