<?php
use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;
?>
<div id="wpadashboard" class="welcome-panel">
	<div class="welcome-panel-content">
		<?php
			echo config::get_option( 'dashboard_contents' );
		?>
	</div>
</div>