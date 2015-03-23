<?php
/*
Plugin Name: Ace Editor
Description: Introduced Ace Editor in file editor.
Text Domain: wp-assistant
*/
namespace WP_Assistant\modules\aceEditor;

use WP_Assistant\modules\module;
use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;

class aceEditor extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );
	}

	/**
	 * ace を登録
	 */
	public function enqueue_scripts( $hook ) {

		if ( 'plugin-editor.php' !== $hook
		     && 'theme-editor.php' !== $hook
		     && 'toplevel_page_wpa_options_page' !== $hook ){
			return false;
		}

		if ( 'plugin-editor.php' == $hook ){
			$default = 'php';
		} else {
			$default = 'css';
		}


		$ace_version = '1.1.8';
		wp_enqueue_script( 'ace-editor', '//cdnjs.cloudflare.com/ajax/libs/ace/' . $ace_version . '/ace.js', array( 'jquery' ), null );
		wp_enqueue_script( 'emmet', '//nightwing.github.io/emmet-core/emmet.js', array( 'ace-editor' ), null );
		wp_enqueue_script( 'ace-editor-emmet', '//cdnjs.cloudflare.com/ajax/libs/ace/' . $ace_version . '/ext-emmet.js', array( 'ace-editor' ), null );
		wp_enqueue_script( 'ace-editor-launguage', '//cdnjs.cloudflare.com/ajax/libs/ace/' . $ace_version . '/ext-language_tools.js', array( 'ace-editor' ), null );
		if ( 'plugin-editor.php' == $hook
		     || 'theme-editor.php' == $hook ){

			wp_enqueue_script( 'ace-editor-init', config::get( 'plugin_url' ) . 'modules/aceEditor/assets/aceinit.js', array( 'ace-editor' ), null );
		}

		// ファイルの拡張子を取得
		$file_name = isset( $_REQUEST['file'] ) ? esc_html( $_REQUEST['file'] ) : $default;
		$pattern   = "/(.*)(?:\.([^.]+$))/";
		preg_match( $pattern, $file_name, $mode );

		$mode = ( isset( $mode[2] ) ) ? $mode[2] : $default;

		/** js に値を渡す */
		wp_localize_script( 'ace-editor-init', 'Ace', array(
			'filename' => $file_name,
			'mode'     => $mode
		) );

		wp_enqueue_style( 'ace-edior-style', plugins_url( '/assets/ace-editor-style.css', __FILE__ ) );

	}

}