<?php
/**
 * 管理画面設定ページHTML
 */
use siteSupports\config;
?>
<?php settings_fields( config::get( 'prefix' ) . 'settings' ); ?>
<form id="ggs_settings_form" action='options.php' method='post'>
	<div class="ggs-message-wrap">
		<div class="ggs-message ggs-message-success updated">
			<h3><span class="dashicons dashicons-update"></span> <?php _e( '設定を保存しました', 'ggsupports' ) ?></h3>
		</div>
		<div class="ggs-message ggs-message-faild error">
			<h3><span class="dashicons dashicons-dismiss"></span> <?php _e( '保存に失敗しました', 'ggsupports' ) ?></h3>
		</div>
	</div><!--/.ggs-message-wrap-->
	<h1><?php bloginfo( 'title' ); ?> <?php _e( '制作支援ツール - 設定画面', 'ggsupports' ) ?></h1>

	<div id="ggs-tabs">
		<ul>
			<li><a href="#ggs-basic-setting"><?php _e( 'サイト設定', 'ggsupports' ) ?></a></li>
			<li><a href="#ggs-dashboard-setting"><?php _e( 'ダッシュボードウィジェット', 'ggsupports' ) ?></a></li>
			<li><a href="#ggs-admin_menu-setting"><?php _e( '管理メニュー', 'ggsupports' ) ?></a></li>
			<li><a href="#ggs-optimize-setting"><?php _e( '最適化', 'ggsupports' ) ?></a></li>
			<li><a href="#ggs-tools-setting"><?php _e( 'ツール', 'ggsupports' ) ?></a></li>
			<li class="pull-right"><button type="submit" name="submit" id="submit" class="button button-primary"><?php _e( '変更を保存', 'ggsupports' ); ?></button><span class="spinner"></span> </li>
		</ul>

		<div id="ggs-basic-setting">
			<p><?php _e( '基本的なサイトの設定をしてください。', 'ggsupports' ); ?><br /></p>
			<div class="acoordion">
			<?php
			do_settings_fields( 'ggs_options_page', 'general_section' );
			?>
			</div>
		</div>

		<div id="ggs-dashboard-setting">
			<p><?php _e( 'ダッシュボードウィジェットのコンテンツを設定してください。※ 通常のスタートウィジェットの代替として表示されます。', 'ggsupports' ) ?><br />
			</p>
			<?php do_settings_fields( config::get( 'prefix' ) . 'options_page', 'dashboard_section'  ); ?>
		</div>

		<div id="ggs-admin_menu-setting">
			<?php
			do_settings_fields( config::get( 'prefix' ) . 'options_page', 'admin_menu_section' );
			?>
		</div>

		<div id="ggs-optimize-setting">
			<div class="acoordion">
			<?php
			do_settings_fields( config::get( 'prefix' ) . 'options_page', 'optimize_section' );
			?>
			</div>
		</div>

		<div id="ggs-tools-setting">
			<h3><?php _e( '設定をエクスポート', 'ggsupports' ); ?></h3>
			<p><?php _e( '設定をエクスポートします。', 'ggsupports' ); ?></p>
			<div class="option-export">
				<button id="option-export" class="button-secondary"><?php _e( '設定をエクスポート', 'ggsupports' ); ?></button>
			</div>
		</div>
	</div>
	<div class="panel-footer">

	</div>
</form>
