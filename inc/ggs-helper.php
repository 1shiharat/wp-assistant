<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Ggs_Helper {

	/**
	 * プラグインオプションの取得
	 *
	 * @param string $option_key
	 *
	 * @return bool|mixed|void
	 */
	public static function get_options( $option_key = '' ) {
		$options = get_option( 'ggsupports_options' );
		if ( $option_key ) {
			if ( isset( $options[ $option_key ] ) ) {
				return $options[ $option_key ];
			}
		} else {
			return $options;
		}

		return false;
	}

	/**
	 * チェックを返す
	 *
	 * @param $option_key
	 * @param $option_value
	 * @param bool $output
	 */
	public static function checked( $option_key, $option_value, $defaults = '' ) {
		$value = self::get_options( $option_key );
		if ( $value == $option_value ) {
			echo 'checked="checked"';

			return;
		}
		if ( $defaults
		     && ( $option_value == $defaults )
		) {
			echo 'checked="checked"';

			return;
		}
	}

	/**
	 * ラジオボックスを作成
	 *
	 * @param array $args
	 */
	public static function radiobox( $args = array() ) {

		$defaults = array(
			'id'      => 'ggsupports_radiobox',
			'label'   => array(
				'true'  => '有効',
				'false' => '無効',
			),
			'value'   => array(
				'true'  => 1,
				'false' => 0,
			),
			'default' => true,
			'desc'    => '',
		);

		$settings = wp_parse_args( $args, $defaults );
		?>
		<div class="form-group form-group-radiobox">
			<p><?php echo $settings['desc'] ?></p>
			<input id="<?php echo $settings['id'] ?>_true" name="<?php echo $settings['id'] ?>" type="radio" value="<?php echo $settings['value']['true'] ?>" <?php Ggs_Helper::checked( $settings['id'], 1, $settings['default'] ) ?> />
			<label for="<?php echo $settings['id'] ?>_true">
				<?php echo $settings['label']['true'] ?>
			</label>
			<input id="<?php echo $settings['id'] ?>_false" name="<?php echo $settings['id'] ?>" type="radio" value="<?php echo $settings['value']['false'] ?>" <?php Ggs_Helper::checked( $settings['id'], 0, $settings['default'] ) ?> />
			<label for="<?php echo $settings['id'] ?>_false">
				<?php echo $settings['label']['false'] ?>
			</label>
		</div>
	<?php
	}
}
