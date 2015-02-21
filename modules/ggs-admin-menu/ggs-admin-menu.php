<?php
class Ggs_Admin_Menu{

	/**
	 * 初期化
	 */
	public function __construct(){

		add_action( 'admin_print_scripts', function(){
			wp_enqueue_script( 'ggs_admin_menu', plugins_url( 'assets/js/admin-menu.js', __FILE__ ), null, null );
			$admin_menus = Ggs_Helper::get_ggs_options( 'ggsupports_admin_menu' );
			wp_localize_script( 'ggs_admin_menu', 'GGS_ADMIN_MENU', array( 'menus' => $admin_menus ) );

		}, 9999 );

		add_action( 'admin_print_scripts', function(){
			$admin_menus = Ggs_Helper::get_ggs_options( 'ggsupports_admin_menu' );
			if ( ! $admin_menus ) {
				return false;
			}
			$menus_array = explode( ',', $admin_menus );
			?>
<script>
(function($){
	$(function(){

	<?php
foreach( $menus_array as $menu ){
		if ( $menu ){ ?>
	$('#<?php echo $menu; ?>').css( 'display', 'none' );
 <?php }
} ?>
	});

})(jQuery);
</script>
		<?php
		}, 999 );


	} // construct


}