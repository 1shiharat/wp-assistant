<?php
/**
 * Class field
 * 各フィールドの根幹クラス
 */

namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\helper;
use WP_Assistant\inc\config;

abstract class field{

	public $type;

	public $settings;
	public $value;

	/**
	 * 初期化
	 */
	public function init(){}

	/**
	 * 出力する
	 */
	abstract public function render();

	public function set() {
		$this->value = config::get_option( $this->field['id'] );
		if ( isset( $this->field['options'] ) ) {
			$this->options = $this->field['options'];
		}
	}

	public function get( $attr ){
		return $this->field[ $attr ];
	}
}