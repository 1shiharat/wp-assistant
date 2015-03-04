<?php
use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;
?>
<div id="wpadashboard" class="original-panel">
	<div class="original-panel-content">
		<?php
			echo html_entity_decode( config::get_option( 'dashboard_contents' ) );
		?>
	</div>
</div>