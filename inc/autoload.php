<?php
/**
 * =====================================================
 * 名前空間からファイルをオートロード
 * @package   WP_Assistant
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace WP_Assistant\inc;


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


	public function autoload( $cls ) {

		$cls = ltrim( $cls, '\\');
		if ( strpos( $cls, 'WP_Assistant' ) !== 0) {
			return;
		}

		$cls = str_replace( 'WP_Assistant', '', $cls );

		$path = untrailingslashit( config::get( 'plugin_dir' ) ) . str_replace('\\', '/', $cls) . '.php';
		require_once( $path );
	}


}
