(function ($) {
	"use strict";
	/**
	 * アコーディオンをすべて開く
	 */
	$.fn.multiAccordion = function () {
		$(this).addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
			.find("h3")
			.addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom")
			.hover(function () {
				$(this).toggleClass("ui-state-hover");
			})
			.prepend('<span class="ui-icon ui-icon-triangle-1-e"></span>')
			.click(function () {
				$(this)
					.toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
					.find("> .ui-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s").end()
					.next().toggleClass("ui-accordion-content-active").slideToggle(100);
				return false;
			})
			.next()
			.addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom")
			.css("display", "block")
			.hide()
			.end().trigger("click");
	};
})(jQuery);

(function ($) {
	"use strict";

	/**
	 * メッセージを表示
	 * @param type メッセージの種類
	 */
	var timer;

	var submit = '#wpa-submit';

	function wpaMessage(type, message) {
		var messageContainer = $('.wpa-message-' + type);
		if (message) {
			messageContainer.html(message);
		}
		var already = 'message-aleady';
		messageContainer.fadeIn();
		clearTimeout(timer);
		if (!messageContainer.hasClass(already)) {
			timer = setTimeout(function () {
				messageContainer.fadeOut('500');
			}, 800);
		}
	}

	function submit_enhanced() {
		$(submit).removeAttr('disabled');
	}

	function changeOnHash() {
		var tab_id = location.hash;
		var tabIndexs = {
			'#wpa-basic-setting': 0,
			'#wpa-dashboard-setting': 1,
			'#wpa-admin-menu-setting': 2
		};
		$('#wpa_tabs').tabs(
			'enable', tab_id
		).addClass("ui-tabs-vertical ui-helper-clearfix");
	}


	$(document).change('#wpa_settings_form *', function () {
		submit_enhanced();
	});

	var submit_flag = true;

	/**
	 * 変更を保存時のイベント
	 * @return void
	 */
	$(document).on('click', submit, function (e) {
		e.preventDefault();
		if (false === submit_flag) {
			return false;
		}
		submit_flag = false;
		var self = $(this);
		$('#wpa_tabs ul').find('.spinner').show();
		console.log($('#wpa_settings_form').serializeArray());
		$.ajax({
			'type': 'post',
			'url': ajaxurl,
			'data': {
				'action': wpaSETTINGS.action,
				'_wp_nonce': wpaSETTINGS._wp_nonce,
				'form': $('#wpa_settings_form').serializeArray(),
			},
			'success': function (data) {
				if (1 == data) {
					$('#wpa_tabs ul').find('.spinner').hide();
					wpaMessage('success');
					self.attr('disabled', 'disabled');
				} else {
					$('#wpa_tabs ul').find('.spinner').hide();
					wpaMessage('faild');
				}
				submit_flag = true;
			}
		});
	});


	/**
	 * wp.media api を利用してメディアアップローダーを開く
	 */
	$(document).on('click', '.wpa-browse', function (event) {
		event.preventDefault();
		var self = $(this);
		var file_frame = null;

		if (file_frame) {
			file_frame.open();
			return false;
		}
		file_frame = wp.media.frames.file_frame = wp.media({
			title: self.data('uploader_title'),
			button: {
				text: self.data('uploader_button_text')
			},
			multiple: false
		});
		file_frame.on('select', function () {
			var attachment = file_frame.state().get('selection').first().toJSON();
			submit_enhanced();
			self.prev('.wpa-url').val(attachment.url);
		});
		file_frame.open();
	});

	/**
	 * ロード時のイベント
	 */
	$(function () {
		window.addEventListener("hashchange", changeOnHash, false);
		$('.acoordion').multiAccordion({
			animate: 100,
			autoHeight: false,
			heightStyle: "content"
		});

		// タブ
		$('#wpa_tabs').tabs().addClass("ui-tabs-vertical ui-helper-clearfix");


		$('#wpa_tabs ul li a').on('click', function () {
			location.hash = $(this).attr('href');
			window.scrollTo(0, 0);
		});

		$('.form-group-radiobox').buttonset();

		$('#wpa-submit').attr('disabled', 'disabled');

	});

})(jQuery);

;(function($){
    "use strict";
    $(function () {
        var welcomeDashboard = $( '#wpadashboard' );
        $( '#dashboard-widgets-wrap' ).prepend( welcomeDashboard );
        $( '#wpa_dashboard_widget' ).remove();
    });
})(jQuery);


(function ($) {
    "use strict";
    /**
     * メニューの配列を整形
     * @param adminMenu
     * @returns {*}
     */
    function getMenuArray(adminMenu) {
        var menus = {};
        if (typeof adminMenu == 'undefined') {
            return false;
        }
        $.each(adminMenu, function (menuKey, menu) {
            $(this).find(".wp-menu-name").find('.pending-count').remove();
            $(this).find(".wp-menu-name").find('.plugin-count').remove();
            var menuName = $(this).find(".wp-menu-name").text();
            var menuID = $(this).attr('id');
            menus[menuKey] = {
                menuName: menuName,
                menuID: menuID
            };
        });
        return menus;
    }

    /**
     * チェックされているidを更新する
     */
    function changeCheckedInput() {
        var checkedMenus = $('#wpa_admin_menus input'),
            checkedMenuID = [];
        $.each(checkedMenus, function (key, checkedMenu) {
            if ($(this).attr('checked') == 'checked') {
                checkedMenuID[key] = $(this).val();
            }
        });

        $('#admin_menu_hidden').val(checkedMenuID.join(','));
    }

    // ロード時のイベント
    $(function () {
        var menuInputRender = $('#wpa_admin_menus'),
            adminMenu = $('#adminmenu > li'),
            menus = getMenuArray(adminMenu),
            savedMenus = wpa_ADMIN_MENU.menus.split(',');

        $.each(menus, function () {
            if (this.menuID && this.menuName) {
                var checked = '';
                if ( $.inArray(this.menuID, savedMenus) >= 0) {
                    checked = ' checked="checked"';
                }
                menuInputRender.append(
					'<div class="menu-list-item">' +
					'<input type="checkbox" name="wpaupports_checkobox[]" value="' + this.menuID + '" ' + checked + '/>' +
					'<span class="menu-list-item-text"> ' + this.menuName +
					'</span>' +
					'<input type="text" class="menu-list-item-text_input" name="wpaupports_checkobox['+ this.menuID +']" value="' + this.menuName.trim() + '" />' +
					'<button class="menu-list-item-text-save" style="display: none"><span class="dashicons dashicons-yes"></span> </button>' +
					'</div>'
				);
            }
        });
        changeCheckedInput();
    });

    // チェックボックスのクリック時イベント
    $(document).on('click', '#wpa_admin_menus input', function () {
        changeCheckedInput();
    });

	// メニュー項目をクリックした時のイベント
	$(document).on('click', '.menu-list-item-text', function(e){
		if ( ! $(this).hasClass('on') )  {
			$(this).addClass('on');
			$(this).hide();
			$(this).next('.menu-list-item-text_input').show();
			$(this).next('.menu-list-item-text_input').next().show();
		} else if ( $(this).next(".menu-list-item-text_input").attr('class') !== $(e.target).attr( 'class' ) ) {
			$(this).removeClass('on');
			$(this).text($(this).find('.menu-list-item-text_input').val());
			$(this).show();
			$(this).next('.menu-list-item-text_input').hide();
		}
	});

	//
	$(document).on('click', '.menu-list-item-text-save', function(e){
		e.preventDefault();
		$(this).hide();
		$(this).prev().hide();
		$(this).prev().prev().show();

	});


})(jQuery);

;(function($){
		var timer;
		var wpaMessage = function (type, message) {
				var messageContainer = $('.wpa-message-' + type);
				if ( message ) {
						messageContainer.html(message);
				}
				var already = 'message-aleady';
				messageContainer.fadeIn();
				clearTimeout(timer);
				if ( ! messageContainer.hasClass(already)) {
						timer = setTimeout(function () {
								messageContainer.fadeOut('500');
						}, 800);
				}
		};
		/**
		 * カウントをリセット
		 * @param target
		 * @returns {boolean}
		 */
		function countReset( target ){
				if ( ! target ){
						return false;
				}
				target = $('.post-count-' + target);
				target.text('0');
		}

		/**
		 * 最適化の実行
		 * @type {boolean}
		 */
		var optimize_flag = true;

		$(document).on('click', '#optimize_submit', function (e) {

				e.preventDefault();
				if ( false === optimize_flag) {
						return false;
				}

				var optimize_wrap = $('#wpa_optimize');
				var optimize_value = {
					revision : $('input[name="optimize_revision"]:checked').val(),
					auto_draft : $('input[name="optimize_auto_draft"]:checked').val(),
					trash : $('input[name="optimize_trash"]:checked').val()
				};


				optimize_flag = false;
				var nonce = $('#optimize_nonce').val();
				$('.run_optimize').find('.spinner').show();
				$.ajax({
						'type': 'post',
						'url': ajaxurl,
						'data': {
								'action': 'run_optimize',
								'_wp_optimize_nonce': nonce,
								'selected_action' : optimize_value
						},
						'success': function (data) {
								$('.run_optimize').find('.spinner').hide();
								if ( data.status == 'faild') {
										wpaMessage('faild', '<h3>' + data.html + '</h3>');
								} else {
										var message = document.createElement('div');
										var heading = document.createElement('h3');
										if ( data.optimize_revision ) {
												$(message).append($(heading).text( $(heading).text() + data.optimize_revision ));
												countReset('revision');
										}
										if (data.optimize_auto_draft) {
												$(message).append($(heading).text( $(heading).text() + data.optimize_auto_draft ));
												countReset('auto_draft');
										}
										if (data.optimize_trash) {
												$(message).append($(heading).text( $(heading).text() + data.optimize_trash ));
												countReset('trash');
										}
										if ( $(message).length > 0 ){
												wpaMessage( 'optimize', $(message) );
										}

								}
								optimize_flag = true;
						}
				});

		});
})(jQuery);

/**
 * 設定をインポート
 */
;(function($){
    "use strict";
    var import_button = '#tools_option_import',
        import_file_input = 'tools_option_import_file',
        import_data = '#tools_option_import_data';

    $(document).on( 'change', '#'+import_file_input, function(){
        var reader = new FileReader(),
            importData;
        var file = document.getElementById( import_file_input ).files[0];
        reader.onload = function(){
            $(import_data).val( reader.result );
        };
        reader.readAsText( file );
    } );

    $(document).on('click', import_button,function(e){
        e.preventDefault();
        var importData;
        var import_data_text = $(import_data).val();
        var reg = /^a:.*;}$/;
        if ( ! import_data_text.match( reg ) ){
            alert( '有効なファイルではありません。ファイルが正しいか確認してください。' );
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
            success : function(data){
                console.log(data);
                if ( data === '1' ){
                    alert( 'オプションを更新しました' );
                    location.reload();
                    return true;
                }
            }
        });
    });
})(jQuery);
