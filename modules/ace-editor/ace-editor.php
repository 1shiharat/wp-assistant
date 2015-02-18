<?php
/**
 * Class Ace_editor
 */
if ( ! class_exists( 'Ace_editor' ) ) {
	class Ace_editor {

		/**
		 * 初期化
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * ace.jsを登録
		 */
		public function enqueue_scripts() {

			if (
				! strpos( $_SERVER['SCRIPT_NAME'], 'theme-editor.php' )
				&&
				! strpos( $_SERVER['SCRIPT_NAME'], 'plugin-editor.php' )
			) {
				return;
			}
			wp_enqueue_script( 'ace-editor', plugins_url( '/assets/ace/src-noconflict/ace.js', __FILE__ ), array( 'jquery' ), null );
			wp_enqueue_script( 'emmet', '//nightwing.github.io/emmet-core/emmet.js', array( 'ace-editor' ), null );
			wp_enqueue_script( 'ace-editor-emmet', plugins_url( '/assets/ace/src-noconflict/ext-emmet.js', __FILE__ ), array( 'ace-editor' ), null );
			wp_enqueue_script( 'ace-editor-launguage', plugins_url( '/assets/ace/src-noconflict/ext-language_tools.js', __FILE__ ), array( 'ace-editor' ), null );
			wp_enqueue_script( 'ace-editor-init', plugins_url( '/aceinit.js', __FILE__ ), array( 'ace-editor' ), null );

			wp_localize_script( 'ace-editor-init', 'Ace', array(
				'filename' => esc_html( $_REQUEST['file'] ),
				'mode'=> substr( esc_html( $_REQUEST['file']  ), -3 )
			) );
			wp_enqueue_style( 'ace-edior-style', plugins_url( '/assets/ace-editor-style.css', __FILE__ ) );
		}

	}
}