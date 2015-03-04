<?php
/**
 * 管理画面設定ページHTML
 */
use WP_Assistant\inc\config;

?>
<?php settings_fields( config::get( 'prefix' ) . 'settings' ); ?>
<form id="wpa_settings_form" method='post'>
	<div class="wpa-message-wrap">
		<div class="wpa-message wpa-message-success updated">
			<h3><span class="dashicons dashicons-update"></span> <?php _e( 'Saved successfully', 'wp-assistant' ) ?></h3>
		</div>
		<div class="wpa-message wpa-message-optimize updated"></div>
		<div class="wpa-message wpa-message-faild error">
			<h3><span class="dashicons dashicons-dismiss"></span> <?php _e( 'Failed to save', 'wp-assistant' ) ?></h3>
		</div>
	</div>
	<!--/.wpa-message-wrap-->
	<h1><span class="dashicons dashicons-admin-settings"></span> <?php bloginfo( 'title' ); ?> <?php _e( 'Setting', 'wp-assistant' ) ?></h1>

	<div id="<?php echo config::get( 'prefix' ); ?>tabs">
		<ul>
			<?php
			foreach( $this->setting_section_names as $section_name => $section_title ) : ?>
				<li><a href="#<?php echo config::get( 'prefix' ) . $section_name; ?>"><?php echo $section_title ?></a></li>
			<?php
			endforeach; ?>
			<li class="pull-right">
				<button type="submit" name="submit" id="submit" class="button button-primary"><?php _e( 'Save', 'wp-assistant' ); ?></button>
				<span class="spinner"></span>
			</li>
		</ul>

		<?php

		foreach( $this->setting_section_names as $section_name => $section_title ) : ?>
			<div id="<?php echo config::get( 'prefix' ) . $section_name; ?>">
				<div class="acoordion">
					<?php
					do_settings_fields( config::get( 'prefix' ) . 'options_page', $section_name ); ?>
				</div>
			</div>
		<?php
		endforeach; ?>


	</div>
	<div class="panel-footer">

	</div>
</form>
