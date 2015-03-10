<?php
/**
 * WYGSIG エディター
 */
namespace WP_Assistant\inc\fields\editor;

use WP_Assistant\inc\field;

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
	}

	public function set() {
		$this->value = config::get_option( $this->field['id'] );
		$this->id    = $field['id'];
	}

	public function render() {
		?>
		<div class="acoordion">
		<p><?php echo $this->desc; ?></p>
		<?php
		$editor_settings = array(
			'wpautop'             => false,
			'media_buttons'       => true,
			'default_editor'      => '',
			'drag_drop_upload'    => true,
			'textarea_name'       => $this->id,
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
		wp_editor( $this->value, $this->id, $editor_settings );
		?>
		</div>
		<?php
	}
}
