<?php
/**
 * モジュールのオートロード
 */
namespace siteSupports;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generic autoloader for classes named in WordPress coding style.
 */
class autoload{

	private $dir = '';

	public function __construct( $dir = '' ) {

		if ( ! empty( $dir ) ) {
			$this->dir = $dir;
		}
		spl_autoload_register( array( '\\' . __NAMESPACE__ . '\autoload', 'autoload' ) );
	}

	function autoload( $cls ) {
		$cls = ltrim( $cls, '\\');
		if ( strpos( $cls, __NAMESPACE__ ) !== 0)
			return;

		$cls = str_replace(__NAMESPACE__, '', $cls);

		$path = untrailingslashit( \siteSupports\config::get( 'plugin_dir' ) ) . str_replace('\\', '/', $cls) . '.php';
		require_once( $path );
	}


}
