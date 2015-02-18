<?php
class Ggs_Admin_Menu{
	public function __construct(){
		add_action( 'admin_print_scripts', function(){
			?>
<script>
;(function($){
	$(function(){
		var admin_menu_wrap = $( '#adminmenu' );
		admin_menu_wrap.find('li').draggable();
		admin_menu_wrap.find('li').droppable();
	})
})(jQuery);
</script>
		<?php
		}, 9999 );
	}
}