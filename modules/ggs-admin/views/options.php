<?php
/**
 * 管理画面設定ページHTML
 */
?>
<form id="ggsupports_settings_form" action='options.php' method='post'>
	<div class="ggs-message-wrap">
		<div class="ggs-message ggs-message-success updated">
			<h3><span class="dashicons dashicons-update"></span> <?php _e( '設定を保存しました', 'ggsupports' ) ?></h3>
		</div>
		<div class="ggs-message ggs-message-faild error">
			<h3><span class="dashicons dashicons-dismiss"></span> <?php _e( '保存に失敗しました', 'ggsupports' ) ?></h3>
		</div>
	</div>

	<?php settings_fields( 'ggsupports_settings' ); ?>
	<h1><?php bloginfo( 'title' ); ?> <?php _e( '制作支援ツール - 設定画面', 'ggsupports' ) ?></h1>
	<div id="<?php echo Ggs_Config::get_prefix(); ?>-tabs">
		<ul>
			<li><a href="#<?php echo Ggs_Config::get_prefix(); ?>-basic-setting"><?php _e( 'サイト設定', 'ggsupports' ) ?></a></li>
			<li><a href="#<?php echo Ggs_Config::get_prefix(); ?>-dashboard-setting"><?php _e( 'ダッシュボードウィジェット', 'ggsupports' ) ?></a></li>
			<li><a href="#<?php echo Ggs_Config::get_prefix(); ?>-admin_menu-setting"><?php _e( '管理メニュー', 'ggsupports' ) ?></a></li>
			<li><a href="#<?php echo Ggs_Config::get_prefix(); ?>-tools-setting"><?php _e( 'ツール', 'ggsupports' ) ?></a></li>
			<li class="pull-right"><button type="submit" name="submit" id="submit" class="button button-primary"><?php _e( '変更を保存', 'ggsupports' ); ?></button><span class="spinner"></span> </li>
		</ul>
		<div id="<?php echo Ggs_Config::get_prefix(); ?>-basic-setting">

			<p><?php _e( '基本的なサイトの設定をしてください。', 'ggsupports' ); ?><br /></p>
			<div class="acoordion">
			<?php
			do_settings_fields( 'ggsupports_options_page', 'ggsupports_general_section' );
			?>
			</div>
		</div>
		<div id="<?php echo Ggs_Config::get_prefix(); ?>-dashboard-setting">
			<p><?php _e( 'ダッシュボードウィジェットのコンテンツを設定してください。※ 通常のスタートウィジェットの代替として表示されます。', 'ggsupports' ) ?><br />
			</p>
			<?php
			settings_fields( 'ggsupports_settings' );
			do_settings_fields( 'ggsupports_options_page', 'ggsupports_dashboard_section'  );
			?>
		</div>
		<div id="<?php echo Ggs_Config::get_prefix(); ?>-admin_menu-setting">
			<?php
			settings_fields( 'ggsupports_settings' );
			do_settings_fields( 'ggsupports_options_page', 'ggsupports_admin_menu_section' );
			?>
		</div>
		<div id="<?php echo Ggs_Config::get_prefix(); ?>-tools-setting">
			<h3>設定のエクスポート</h3>
			<p>
			<?php
				_e( '設定をエクスポートします。', 'ggsupports' );
			?>
			</p>

			<div class="option-export">
				<button id="option-export" class="button-secondary">設定をエクスポート</button>
			</div>

		</div>
	</div>
	<div class="panel-footer">

	</div>
</form>
