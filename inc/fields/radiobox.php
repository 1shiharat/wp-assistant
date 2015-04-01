<?php
/**
 * WYGSIG エディター
 */
namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\helper;

class radiobox extends field {

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
		if ( ! $this->options ){
			$this->options = array(
				'1' => __( 'ON', 'wp-assistant' ),
				'0' => __( 'OFF', 'wp-assistant' ),
			);
		}
		?>
		<div class="form-group form-group-radiobox">
		<?php
		foreach ( $this->options as $key => $option ) {
			?>
			<input id="<?php echo $this->get( 'id' ) . '_' . $key; ?>" value="<?php echo $key ?>" name="<?php echo $this->get( 'id' ) ?>" type="radio" class="ui-helper-hidden-accessible" <?php helper::checked( $this->get( 'id' ), $key, $this->get( 'default' ) ) ?>/>
			<label for="<?php echo $this->get( 'id' ) . '_' . $key; ?>" class="ui-button ui-widget ui-state-default ui-button-text-only ui-corner-left"><?php echo $option; ?></label>
		<?php
		}
		?>
		</div>
		<?php
	}
}
