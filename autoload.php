<?php
if ( ! class_exists( 'GGSupports_Autoload' ) ) {
	/**
	 * Generic autoloader for classes named in WordPress coding style.
	 */
	class GGSupports_Autoload {

		private $dir = '';

		public function __construct( $dir = '' ) {

			if ( ! empty( $dir ) ) {
				$this->dir = $dir;
			}
			spl_autoload_register( array( $this, 'spl_autoload_register' ) );
		}

		public function spl_autoload_register( $class_name ) {
			$class_path = $this->dir . '/' . strtolower( str_replace( '_', '-', $class_name ) ). '/' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
			if ( file_exists( $class_path ) ){
				include $class_path;
			}
		}

	}
}