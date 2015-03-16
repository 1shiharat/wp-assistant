<?php
/**
 * WYGSIG エディター
 */
namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\helper;

class media extends field {

	public $options = array();

	/**
	 * 初期化
	 *
	 * @param $field
	 */
	public function __construct( $field ) {
		$this->field = $field;
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );
		$this->set();
		$this->render();
	}

	public function enqueue_scripts( $hook ){
		wp_enqueue_media();
	}

	public function render() {
    ?>
      <input type="text" class="wpa-url" name="<?php echo $this->get( 'id' ); ?>" id="<?php echo $this->get( 'id' ); ?>" value="<?php echo $this->value ?>" />
      <input type="button" class="button wpa-browse" value="<?php _e( 'Select File', 'wp-assistant' ); ?>" />
		<?php
	}
}
