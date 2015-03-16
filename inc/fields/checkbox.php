<?php
/**
 * WYGSIG エディター
 */
namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\fields;

class checkbox extends field {

	public $id = '';
	public $value = '';
	public $field = '';
	public $options = array();

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
		if ( isset( $this->field['options'] ) ) {
			$this->options = $this->field['options'];
		}

	}

	public function render() {
		if ( $this->options ) {
			return false;
		}

		foreach( $this->options as $option ){
				?>
				<input id="<?php echo $this->id ?>" name="<?php echo $this->id ?>" type="checkbox" <?php helper::checked( $this->value, $field['value']  ) ?>/>
			<?php
		}

	}
}
