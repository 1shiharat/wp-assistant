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
		<div class="wpa-message wpa-message-no-update error">
			<h3><span class="dashicons dashicons-dismiss"></span> <?php _e( 'There is no content to be updated', 'wp-assistant' ) ?></h3>
		</div>
	</div>
	<!--/.wpa-message-wrap-->

	<h1><span class="dashicons dashicons-admin-settings"></span> <?php bloginfo( 'title' ); ?> <?php _e( 'Setting', 'wp-assistant' ) ?> </h1><span class="wpa-label wp-label-blue" for="">version <?php echo config::get('version') ?></span>
	<style id="wpa_tabs_hide">
		#wpa_tabs{
			display: none;
			opacity: 0;
		}
	</style>
	<div id="<?php echo config::get( 'prefix' ); ?>tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-vertical ui-helper-clearfix">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<?php
			foreach( $this->settings as $section_name => $section ) : ?>
				<li class="ui-state-default ui-corner-top "><a href="#<?php echo config::get( 'prefix' ) . $section['section']['id']; ?>"><span class="dashicons dashicons-menu"></span> <?php echo $section['section']['tabs_name'] ?></a></li>
			<?php
			endforeach; ?>
			<li class="pull-right">
				<button type="submit" name="submit" id="wpa-submit" class="button button-primary"><?php _e( 'Save', 'wp-assistant' ); ?></button>
				<span class="spinner"></span>
			</li>
		</ul>
		<?php
		foreach( $this->settings  as $section_name => $section ) : ?>
			<div id="<?php echo config::get( 'prefix' ) . $section['section']['id']; ?>" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
				<?php
				$this->do_settings_fields( config::get( 'prefix' ) . 'options_page', $section['section']['id'] ); ?>
			</div>
		<?php
		endforeach; ?>
	</div>
	<div class="panel-footer">

	</div>
</form>
