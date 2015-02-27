<?php
namespace siteSupports\inc;

use siteSupports\config;
use siteSupports\inc\helper;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class helper {

	/**
	 * チェックを返す
	 *
	 * @param $option_key
	 * @param $option_value
	 * @param bool $output
	 */
	public static function checked( $option_key, $option_value, $default = false ) {
		$value = config::get_option( $option_key );

		if ( $value == $option_value ) {
			echo 'checked="checked"';
			return;
		}

		if ( ! $value && $default && $option_value == $default ) {
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
			'id'      => 'ggs_radiobox',
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
			<input id="<?php echo $settings['id'] ?>_true" name="<?php echo $settings['id'] ?>" type="radio" value="<?php echo $settings['value']['true'] ?>" <?php static::checked( $settings['id'], 1, $settings['default'] ) ?> />
			<label for="<?php echo $settings['id'] ?>_true">
				<?php echo $settings['label']['true'] ?>
			</label>
			<input id="<?php echo $settings['id'] ?>_false" name="<?php echo $settings['id'] ?>" type="radio" value="<?php echo $settings['value']['false'] ?>" <?php static::checked( $settings['id'], 0, $settings['default'] ) ?> />
			<label for="<?php echo $settings['id'] ?>_false">
				<?php echo $settings['label']['false'] ?>
			</label>
		</div>
	<?php
	}
}
