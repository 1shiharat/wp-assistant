<?php
namespace WP_Assistant\inc;

use WP_Assistant\inc\config;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class helper {

	/**
	 * チェックを返す
	 *
	 * @param $option_key
	 * @param $option_value
	 * @param bool $default
	 *
	 * @internal param bool $output
	 */
	public static function checked( $option_key, $option_value, $default = false ) {
		$value = config::get_option( $option_key );

		if ( (string) $value === (string) $option_value ) {
			echo 'checked="checked"';
			return;
		}

		if ( !$value && $default && $option_value == $default ) {
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
			'id'      => 'wpa_radiobox',
			'label'   => array(
				'true'  => __( 'ON', 'wp-assistant' ),
				'false' => __( 'OFF', 'wp-assistant' ),
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
		</div>
	<?php
	}

	public static function textarea( $args = array() ){
		$defaults = array(
			'id'      => 'wpa_textarea',
			'value'   => '',
			'default' => '',
			'desc'    => '',
		);
		$settings = wp_parse_args( $args, $defaults );
		?>
		<div class="form-group form-group-radiobox">
			<p><?php echo $settings['desc'] ?></p>
			<textarea name="<?php echo $settings['id'] ?>" id="<?php echo $settings['id'] ?>" cols="30" rows="10"><?php echo esc_html( $settings['value'] ) ?></textarea>
		</div>
		</div>
		<?php
	}


	/**
	 * ユーザードロップダウンの出力
	 *
	 * @param string $args
	 *
	 * @return string
	 */
	public static function dropdown_users( $args = '' ) {
		$defaults = array(
			'show_option_all'         => '',
			'show_option_none'        => '',
			'hide_if_only_one_author' => '',
			'orderby'                 => 'display_name',
			'order'                   => 'ASC',
			'include'                 => '',
			'exclude'                 => '',
			'multi'                   => 0,
			'show'                    => 'display_name',
			'echo'                    => 1,
			'selected'                => 0,
			'name'                    => 'user',
			'class'                   => '',
			'id'                      => '',
			'blog_id'                 => $GLOBALS['blog_id'],
			'who'                     => '',
			'include_selected'        => false,
			'option_none_value'       => - 1
		);

		$defaults['selected'] = is_author() ? get_query_var( 'author' ) : 0;

		$r                 = wp_parse_args( $args, $defaults );
		$show              = $r['show'];
		$show_option_all   = $r['show_option_all'];
		$show_option_none  = $r['show_option_none'];
		$option_none_value = $r['option_none_value'];

		$query_args           = wp_array_slice_assoc( $r, array(
			'blog_id',
			'include',
			'exclude',
			'orderby',
			'order',
			'who'
		) );
		$query_args['fields'] = array( 'ID', 'user_login', $show );
		$users                = get_users( $query_args );

		$output = '';
		if ( ! empty( $users ) && ( empty( $r['hide_if_only_one_author'] ) || count( $users ) > 1 ) ) {
			$name = esc_attr( $r['name'] );
			if ( $r['multi'] && ! $r['id'] ) {
				$id = '';
			} else {
				$id = $r['id'] ? " id='" . esc_attr( $r['id'] ) . "'" : " id='$name'";
			}
			$output = "<select name='{$name}'{$id} class='" . $r['class'] . "' multiple>\n";

			if ( $show_option_all ) {
				$output .= "\t<option value='0'>$show_option_all</option>\n";
			}

			if ( $show_option_none ) {
				$_selected = selected( $option_none_value, $r['selected'], false );
				$output .= "\t<option value='" . esc_attr( $option_none_value ) . "'$_selected>$show_option_none</option>\n";
			}

			$found_selected = false;
			$i              = 0;
			foreach ( (array) $users as $user ) {
				$user->ID = (int) $user->ID;
				if ( is_array( $r['selected'] )
				     && in_array( $user->ID, $r['selected'] )
				) {
					$_selected = ' selected="selected"';
				} else {
					$_selected = "";
				}

				if ( $_selected ) {
					$found_selected = true;
				}
				$display = ! empty( $user->$show ) ? $user->$show : '(' . $user->user_login . ')';
				$output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
				$i ++;
			}

			if ( $r['include_selected'] && ! $found_selected && ( $r['selected'] > 0 ) ) {
				$user      = get_userdata( $r['selected'] );
				$_selected = selected( $user->ID, $r['selected'], false );
				$display   = ! empty( $user->$show ) ? $user->$show : '(' . $user->user_login . ')';
				$output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
			}
			$output .= "</select>";
		}

		$html = $output;

		if ( $r['echo'] ) {
			echo $html;
		}

		return $html;
	}
}
