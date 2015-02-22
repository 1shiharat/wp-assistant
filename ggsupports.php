<?php
/*
Plugin Name: GrowGroup - サイト制作サポート
Plugin URI: http://grow-group.jp/
Description: Webサイト制作のお助けプラグイン
Author: 1shiharaT
Version: 1.0.0
Author URI: http://grow-group.jp/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


require 'autoload.php';
require 'inc/ggs-helper.php';
add_action( 'plugins_loaded', array( 'GGSupports', 'get_instance' ) );


/**
 * Class GGSupports_Config
 * プラグインの設定
 */
class Ggs_Config{

	public static $prefix = 'ggs';

	public static $version = '1.0.0';

	public static $plugin_url = '';

	/**
	 * プレフィックスを取得
	 * @return string
	 */
	public static function get_prefix(){
		return self::$prefix;
	}

	/**
	 * プレフィックスを取得
	 * @return string
	 */
	public static function get_version(){
		return self::$version;
	}

	public static function get_ggs_options( $option_key = '' ){
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
class GGSupports {

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
		new GGSupports_Autoload( __DIR__ . '/modules' );

		// wp_headやwp_footerなどから余計な記述を削除
		add_action( 'plugins_loaded', array( new Ggs_Cleanup(), '__construct' ), 10 );
		add_action( 'plugins_loaded', array( new Ggs_Admin_Menu(), '__construct' ), 10 );

		// Ace Editor
		add_action( 'admin_init', array( new Ace_editor(), '__construct' ), 10 );
		add_action( 'admin_init', array( new Ggs_Admin(), '__construct' ), 10 );

		// CF7拡張
		add_action( 'wp', array( new Cf7_ajaxzip3(), 'cf7_ajaxzip3' ), 10 );
	}
}