<?php
/**
 * 管理画面設定ページHTML
 */
use WP_Assistant\inc\config;

?>
<?php settings_fields( config::get( 'prefix' ) . 'settings' ); ?>
<form id="ggs_settings_form" action='options.php' method='post'>
	<div class="ggs-message-wrap">
		<div class="ggs-message ggs-message-success updated">
			<h3><span class="dashicons dashicons-update"></span> <?php _e( 'Saved successfully', 'wp-assistant' ) ?></h3>
		</div>
		<div class="ggs-message ggs-message-optimize updated"></div>
		<div class="ggs-message ggs-message-faild error">
			<h3><span class="dashicons dashicons-dismiss"></span> <?php _e( 'Failed to save', 'wp-assistant' ) ?></h3>
		</div>
	</div>
	<!--/.ggs-message-wrap-->
	<h1><?php bloginfo( 'title' ); ?> <?php _e( 'Supports ', 'wp-assistant' ) ?></h1>

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
