<?php
/**
 * テキストエリア
 */
namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\helper;

class text extends field {

	public $options = array();

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
		?>
		<input type="text" class="wpa-text" name="<?php echo $this->get( 'id' ); ?>" id="<?php echo $this->get( 'id' ); ?>" value="<?php echo $this->value ?>" />
	<?php
	}
}
