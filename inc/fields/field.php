<?php
/**
 * Class field
 * 各フィールドの根幹クラス
 */

namespace WP_Assistant\inc\fields\field;

abstract class field{

	public $type;

	public $settings;

	/**
	 * 初期化
	 */
	public function init(){}

	/**
	 * デフォルトの値を指定
	 */
	public function defaults_settings(){}

	/**
	 * 出力する
	 */
	public function output(){}


}