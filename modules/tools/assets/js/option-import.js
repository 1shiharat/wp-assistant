;(function($){
	"use strict";
	var tools = {
		import_button : '#tools_option_import',
		import_file_input : 'tools_option_import_file',
		import_data : '#tools_option_import_data',
		init: function(){
			tools.event();
		},
		event: function(){
			$(document).on( 'change', '#'+tools.import_file_input, function(){
				var reader = new FileReader(),
				importData;
				var file = document.getElementById( tools.import_file_input ).files[0];
				reader.onload = function(){
					$(tools.import_data).val( reader.result );
				};
				reader.readAsText( file );
			} );

			$(document).on('click', tools.import_button,function(e){
				e.preventDefault();
				var importData;
				var import_data_text = $(tools.import_data).val();
				if ( import_data_text.length <= 0 ){
					import_data_text = $('#import_textarea').val();
				}
				var reg = /^a:.*;}$/;
				if ( ! import_data_text.match( reg ) ){
					wpa.message('faild', '<h3>' + '更新データが正しくありません' + '</h3>');
					return false;
				}
				var wp_import_nonce = $('#tools_option_import_nonce').val();
				$.ajax({
					type : 'post',
					url: ajaxurl,
					data : {
						action : 'wpa_option_import',
						wp_import_nonce : wp_import_nonce,
						import_data: import_data_text
					},
				}).done(function(data){
					if ( data === '1' ){
						wpa.message('success');
						setTimeout(function(){
							location.reload();
						}, 300);
						return true;
					} else {
						wpa.message('faild', '<h3>' + '更新データが正しくありません' + '</h3>');
						return false;
					}
				});
			});
		}
	}; // end .import

	$(function(){
		wpa.tools= tools;
		wpa.tools.init();
	});

})(jQuery);
