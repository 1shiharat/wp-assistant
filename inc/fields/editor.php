<?php
/**
 * WYGSIG エディター
 */
namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\config;

class editor extends field {

	public $id = '';
	public $value = '';
	public $field = '';

	/**
	 * 初期化
	 *
	 * @param $field
	 */
	public function __construct( $field ) {
		$this->field = $field;
		$this->set();
		$this->render();
	}

	public function render() {
		$editor_settings = array(
			'wpautop'             => false,
			'media_buttons'       => true,
			'default_editor'      => '',
			'drag_drop_upload'    => true,
			'textarea_name'       => $this->get( 'id' ),
			'textarea_rows'       => 50,
			'tabindex'            => '',
			'tabfocus_elements'   => ':prev,:next',
			'editor_css'          => '',
			'editor_class'        => '',
			'teeny'               => false,
			'dfw'                 => false,
			'_content_editor_dfw' => false,
			'tinymce'             => false,
			'quicktags'           => true
		);
		echo wp_editor( $this->value, $this->get( 'id' ), $editor_settings );
	}
}
