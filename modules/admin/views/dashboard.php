<?php
use siteSupports\config;
use siteSupports\inc\helper;
?>
<div id="ggsdashboard" class="welcome-panel">
	<div class="welcome-panel-content">
		<?php
			echo config::get_option( 'dashboard_contents' );
		?>
	</div>
</div>