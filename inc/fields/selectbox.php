<?php
/**
 * WYGSIG エディター
 */
namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\helper;

class selectbox extends field {

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
		if ( ! $this->options ) {
			$this->options = array(
				'true'  => __( 'ON', 'wp-assistant' ),
				'false' => __( 'OFF', 'wp-assistant' ),
			);
		}
		?>
		<div class="form-group form-group-selectbox">
			<select name="<?php echo $this->get( 'id' ) ?>" id="<?php echo $this->get( 'id' ) ?>">
				<?php
				foreach ( $this->options as $key => $option ) {
					?>
					<option value="<?php echo $key ?>"<?php helper::checked( $this->get( 'id' ), $this->value, $this->get( 'default' ) ) ?>>
						<?php echo $option ?>
					</option>
				<?php
				}
				?>
			</select>
		</div>
	<?php
	}
}
