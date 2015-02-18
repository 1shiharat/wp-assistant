<?php
/**
 * 管理画面設定ページHTML
 */

?>
<form action='options.php' method='post'>
	<?php settings_fields( 'ggsupports_settings' ); ?>
	<h1><?php echo esc_html( get_bloginfo( 'title' ) ); ?> 制作支援ツール - 設定画面</h1>
	<p><?php submit_button(); ?></p>
	<div id="<?php echo Ggs_Config::get_prefix(); ?>-tabs">
		<ul>
			<li><a href="#<?php echo Ggs_Config::get_prefix(); ?>-basic-setting">サイト設定</a></li>
			<li><a href="#<?php echo Ggs_Config::get_prefix(); ?>-dashboard-setting">ダッシュボードウィジェット</a></li>
		</ul>
		<div id="<?php echo Ggs_Config::get_prefix(); ?>-basic-setting">

			<p>基本的なサイトの設定をしてください。<br /></p>
			<div class="acoordion">
			<?php
			do_settings_fields( 'ggsupports_options_page', 'ggsupports_general_section' );
			?>
			</div>
		</div>
		<div id="<?php echo Ggs_Config::get_prefix(); ?>-dashboard-setting">

			<p>ダッシュボードウィジェットのコンテンツを設定してください。<br />
			<small>※通常のスタートウィジェットの代替として表示されます。</small></p>
			<?php
			settings_fields( 'ggsupports_settings' );
			do_settings_fields( 'ggsupports_options_page', 'ggsupports_dashboard_section'  );
			?>
		</div>
	</div>
</form>
