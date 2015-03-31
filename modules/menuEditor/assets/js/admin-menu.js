(function ($) {
	"use strict";

	var deparam = function (querystring) {

		querystring = querystring.substring(querystring.indexOf('?') + 1).split('&');
		var params = {}, pair, d = decodeURIComponent, i, r;

		for (r=0; r < querystring.length;r++) {
			params[r] = {};
		}
		// クエリストリングから変換
		for (i = querystring.length; i > 0;) {
			pair = querystring[--i].split('=');

			if ( pair[0] && pair[0] !== 'undefined' ) {
				var key = pair[0];
				var num = key.match(/\d/g).join('').trim();
				var param_key = d(pair[0].match(/\D/g).join(''));
				params[parseInt(num)][param_key] = d(pair[1].replace(/\+/g, " "));
			}
		}

		// 余計なオブジェクトを削除
		$.each(params,function(key){
			if ( typeof this.id == "undefined" ){
				delete(params[key]);
			}
		});

		return params;

	};

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
	 * チェックされているIDを更新する
	 */
	function changeCheckedInput() {
		var checkedMenus = $('#wpa_admin_menus .adminmenu_hidden_check'),
			checkedMenuID = [];
		var admin_menu_title = $('.menu-list-item-text_input');
		var am_array = '';

		var i = 0;
		$.each(admin_menu_title, function (key, data) {
			var menu_id = $(this).data('menu-id');
			var menu_title = $(this).val().replace(/\s+/g, "+");
			var menu_disp_check = $(this).prev().prev();
			var menu_disp = '';
			if ( menu_disp_check.attr('checked') ) {
				menu_disp = 0;
			} else {
				menu_disp = 1;
			}

			if (menu_title) {
				if ( i === 0 ){
					am_array += i + 'id=' + menu_id;
					am_array += '&' + i + 'title=' + menu_title;
					am_array += '&' + i + 'disp=' + menu_disp;
				} else {
					am_array += '&' + i + 'id=' + menu_id;
					am_array += '&' + i + 'title=' + menu_title;
					am_array += '&' + i + 'disp=' + menu_disp;
				}
			}
			i++;
		});

		$('#admin_menu_hidden').val(am_array);
	}

	/**
	 * ロード時のイベント
	 */
	$(function () {
		var menuInputRender = $('#wpa_admin_menus'),
			adminMenu = $('#adminmenu > li'),
			menus = getMenuArray(adminMenu);
		var savedMenus = deparam(wpa_ADMIN_MENU.menus);

		var i = 0;

		$.each(menus, function () {
			if (this.menuID && this.menuName) {
				var checked = '';

				var title = this.menuName;

				if ( typeof savedMenus[i] !== "undefined"
					 && typeof savedMenus[i].id !== "undefined"
					 && this.menuID === savedMenus[i].id ) {
					if (savedMenus[i].disp == 0) {
						checked = ' checked="checked"';
					}
					var title = savedMenus[i].title;
				}


				menuInputRender.append(
					'<div class="menu-list-item">' +
					'<input type="checkbox" class="admin_menu_edit adminmenu_hidden_check" name="wpa_supports_checkobox[' + this.menuID + '][disp]" value="1" ' + checked + '/>' +
					'<span class="menu-list-item-text"> ' + title +
					'</span>' +
					'<input type="text" class="admin_menu_edit menu-list-item-text_input" data-menu-id="' + this.menuID + '" name="wpa_supports_checkobox[' + this.menuID + '][name]" value="' + title + '" />' +
					'<button class="menu-list-item-text-save" style="display: none"><span class="dashicons dashicons-yes"></span> </button>' +
					'</div>'
				);
				i++;
			}
		});
		changeCheckedInput();
	});

	/**
	 * チェックボックスのクリック時イベント
	 */
	$(document).on('click', '#wpa_admin_menus input', function () {
		changeCheckedInput();
	});

	$(document).on('click','#wpa_admin_menus input[type="checkbox"]',function(){
		var menuid = $(this).next().next().data('menu-id');
		var checked = $(this).attr('checked');

		if ( checked === 'checked' ){
			$('#' + menuid).fadeOut();
		} else{
			$('#' + menuid).fadeIn();
		}

	})



	/**
	 * メニュー項目をクリックした時のイベント
	 */
	$(document).on('click', '.menu-list-item-text', function (e) {
		var parentList = $(this).closest('.menu-list-item');
		var input = $(this).next('.menu-list-item-text_input');

		if (!parentList.hasClass('on')) {
			parentList.addClass('on');
			$(this).hide();
			input.show();
			input.next().show();
		} else if ($(this).next(".menu-list-item-text_input").attr('class') !== $(e.target).attr('class')) {
			parentList.removeClass('on');
			$(this).text(input.val());
			$(this).show();
			input.hide();
		}
	});

	/**
	 * セーブボタンをクリックした時のイベント
	 */
	$(document).on('click', '.menu-list-item-text-save', function (e) {
		e.preventDefault();
		var input_value = $(this).prev('.menu-list-item-text_input').val();
		$(this).prev().prev().fadeOut().text(input_value).fadeIn();
		var menuid = $(this).prev().data('menu-id');
		$('#' + menuid ).find('.wp-menu-name').fadeOut().text(input_value).fadeIn();
		$(this).hide();
		$(this).prev().hide();
		$(this).prev().prev().show();
		changeCheckedInput();

	});


})(jQuery);
