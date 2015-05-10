<?php
/*
Plugin Name: WP Assistant
Plugin URI: http://grow-group.jp/
Description: This plugin to provide a convenient function when build WordPress site.
Author: 1shiharaT
Version: 0.3.0
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
		if ( ! get_option( 'wpa_options' ) ){
			$defaults = unserialize( 'a:23:{s:23:"modules_list_cf7AjaxZip";s:1:"0";s:23:"modules_list_menuEditor";s:1:"1";s:22:"modules_list_aceEditor";s:1:"1";s:21:"show_current_template";s:1:"1";s:10:"feed_links";s:1:"0";s:7:"favicon";s:0:"";s:12:"wp_generator";s:1:"0";s:20:"wp_shortlink_wp_head";s:1:"0";s:7:"wpautop";s:1:"0";s:8:"revision";s:1:"0";s:6:"jquery";s:1:"0";s:9:"bootstrap";s:1:"0";s:6:"xmlrpc";s:1:"0";s:14:"author_archive";s:1:"0";s:14:"admin_page_nav";s:1:"1";s:18:"dashboard_contents";s:0:"";s:10:"admin_menu";s:998:"0id=menu-dashboard&0text=ダッシュボード&0link=aW5kZXgucGhw&0disp=1&0target=&0order=0&1id=menu-posts&1text=投稿&1link=ZWRpdC5waHA=&1disp=1&1target=&1order=1&2id=menu-media&2text=メディア&2link=dXBsb2FkLnBocA==&2disp=1&2target=&2order=2&3id=menu-pages&3text=固定ページ&3link=ZWRpdC5waHA/cG9zdF90eXBlPXBhZ2U=&3disp=1&3target=&3order=3&4id=menu-comments&4text=コメント&4link=ZWRpdC1jb21tZW50cy5waHA=&4disp=1&4target=&4order=4&5id=menu-appearance&5text=外観&5link=dGhlbWVzLnBocA==&5disp=1&5target=&5order=5&6id=menu-plugins&6text=プラグイン&6link=cGx1Z2lucy5waHA=&6disp=1&6target=&6order=6&7id=menu-users&7text=ユーザー&7link=dXNlcnMucGhw&7disp=1&7target=&7order=7&8id=menu-tools&8text=ツール&8link=dG9vbHMucGhw&8disp=1&8target=&8order=8&9id=menu-settings&9text=設定&9link=b3B0aW9ucy1nZW5lcmFsLnBocA==&9disp=1&9target=&9order=9&10id=toplevel_page_wpa_options_page&10text=WP+Assistant&10link=YWRtaW4ucGhwP3BhZ2U9d3BhX29wdGlvbnNfcGFnZQ==&10disp=1&10target=&10order=10";s:16:"login_panel_logo";s:0:"";s:22:"login_panel_background";s:0:"";s:17:"admin_footer_text";s:0:"";s:17:"optimize_revision";s:4:"true";s:19:"optimize_auto_draft";s:4:"true";s:14:"optimize_trash";s:4:"true";}' );
			update_option( config::get( 'prefix' ) . 'options',  $defaults );
		}
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
