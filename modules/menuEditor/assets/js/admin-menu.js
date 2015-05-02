(function ($) {
	"use strict";

	var adminMenuEditor = {
		insertTarget: '#wpa_admin_menus_list',
		saveHiddenInput: '#admin_menu_hidden',
		checkedMenus: '#wpa_admin_menus .adminmenu_hidden_check',
		menuListTemplateID: '#wpa_admin_menus_template',
		adminMenu: '#adminmenu > li',
		/**
		 * データベースに保存する値を更新
		 */
		update: function () {
			var checkedMenus = $(adminMenuEditor.checkedMenus),
				menuListText = $('.menu-list-item-text_input'),
				menuListTextStr = '',
				i = 0;

			$.each(menuListText, function () {
				var id = $(this).data('menu-id'),
					text = $(this).val().replace(/\s+/g, "+");
				var disp = ( $(this).prev().prev().attr('checked') ) ? 0 : 1;

				var order = $(this).closest('.menu-list-item').data('order');

				if (text) {
					if (i === 0) {
						menuListTextStr += i + 'id=' + id;
						menuListTextStr += '&' + i + 'text=' + text;
						menuListTextStr += '&' + i + 'disp=' + disp;
						menuListTextStr += '&' + i + 'order=' + i;
					} else {
						menuListTextStr += '&' + i + 'id=' + id;
						menuListTextStr += '&' + i + 'text=' + text;
						menuListTextStr += '&' + i + 'disp=' + disp;
						menuListTextStr += '&' + i + 'order=' + i;
					}
					i++;
				}

			});
			$(adminMenuEditor.saveHiddenInput).val(menuListTextStr);
		},

		/**
		 * クエリストリングから配列に変換
		 * @param querystring
		 * @returns {{}}
		 */
		deparam: function (querystring) {
			querystring = querystring.substring(querystring.indexOf('?') + 1).split('&');
			var params = {}, pair, d = decodeURIComponent, i, r;

			for (r = 0; r < querystring.length; r++) {
				params[r] = {};
			}
			// クエリストリングから変換
			for (i = querystring.length; i > 0;) {
				pair = querystring[--i].split('=');
				if (pair[0] && pair[0] !== 'undefined') {
					var key = pair[0];
					// 0.1.7以下のバージョンに対応
					if (!key.match(/\d/g)) {
						return params;
					}
					var num = key.match(/\d/g).join('').trim();
					var param_key = d(pair[0].match(/\D/g).join(''));
					params[parseInt(num)][param_key] = d(pair[1].replace(/\+/g, " "));
				}
			}
			// 余計な情報を削除
			$.each(params, function (key) {
				if (typeof this.id == "undefined") {
					delete(params[key]);
				}
			});
			return params;
		},

		/**
		 * メニューエディタを挿入するjQueryオブジェクトを返す
		 * @returns {*|HTMLElement}
		 */
		getTarget: function () {
			return $(adminMenuEditor.insertTarget);
		},

		/**
		 * もともとのメニューを返す
		 * @returns {*|HTMLElement}
		 */
		getOriginalMenu: function () {
			return $(adminMenu);
		},

		/**
		 * もともとのメニューをオブジェクトに整形
		 * @param originalMenu
		 * @returns {*}
		 */
		getOriginalMenuArray: function () {
			var originalMenu = adminMenuEditor.getOriginalMenu(),
				menus = {};

			if (typeof originalMenu == 'undefined') {
				return false;
			}

			$.each(originalMenu, function (menuKey, menu) {
				$(this).find(".wp-menu-name").find('.pending-count').remove();
				$(this).find(".wp-menu-name").find('.plugin-count').remove();
				var text = $(this).find(".wp-menu-name").text();
				var id = $(this).attr('id');
				menus[menuKey] = {
					title: text,
					id: id
				};
			});

			return menus;
		},

		/**
		 * テンプレートをセット
		 */
		setTemplate: function () {
			adminMenuEditor.template = _.template($(adminMenuEditor.menuListTemplateID).html());
		},

		/**
		 * オリジナルのメニューを取得
		 * @return {{menus: *}}
		 */
		getMenus: function () {
			return {
				menus: adminMenuEditor.getOriginalMenuArray()
			};
		},

		formatMenu: function (menus) {

		},

		/**
		 * すでに保存してある値を取得
		 * @param menus
		 * @returns {{menus: {}}}
		 */
		getData: function (menus) {
			var menuObj = adminMenuEditor.deparam(menus);
			menuObj = _.sortBy( menuObj, function(menu){ return parseInt( menu.order ); } )

			return {
				menus: menuObj
			};
		},

		/**
		 * 初期化
		 * @param wpa_ADMIN_MENU
		 */
		init: function (settings) {
			var self = adminMenuEditor;
			if ( $(adminMenuEditor.menuListTemplateID).length ) {
				self.setTemplate();
				var defaults = adminMenuEditor.defaults();
				var menus = settings.menus || defaults;

				adminMenuEditor.enhanced(self.getData(menus));
				var compiledHtml = self.compiled(self.getData(menus));
				self.getTarget().html(compiledHtml);
				adminMenuEditor.update();
				adminMenuEditor.event();
			} else {
				var defaults = adminMenuEditor.defaults();
				var menus = settings.menus || defaults;
				adminMenuEditor.enhanced(self.getData(menus));
			}
		},

		/**
		 * テンプレートをコンパイル
		 * @param menus
		 * @returns {*}
		 */
		compiled: function (menus) {
			return adminMenuEditor.template(menus);
		},

		enhanced: function(settings){
			var adminMenu = _.clone( $('#adminmenu') );
			_.each(settings.menus,function(menu, key){
				var target = adminMenu.find('#'+menu.id);
				target.find('.wp-menu-name').text(menu.text);
				target.attr('data-order', menu.order);
			});
			adminMenu.html( _.sortBy(adminMenu.children(), function(menu){ return parseInt( $(menu).data('order') ) }) );

		},
		defaults: function(){
			var menuStr = '';
			var i = 0;
			var adminMenu = _.clone( $(adminMenuEditor.adminMenu) );
			adminMenu.each(function(){
				var id = $(this).attr('id');
				var text = $(this).find('.wp-menu-name').text().replace(/(^\s+)|(\s+$)/g, "");
				if ( text ) {
					if (i === 0) {
						menuStr += i + 'id=' + id;
						menuStr += '&' + i + 'title=' + text;
						menuStr += '&' + i + 'disp=' + 1;
						menuStr += '&' + i + 'order=' + i;
					} else {
						menuStr += '&' + i + 'id=' + id;
						menuStr += '&' + i + 'title=' + text;
						menuStr += '&' + i + 'disp=' + 0;
						menuStr += '&' + i + 'order=' + i;
					}
				}
				i++;
			});
			return menuStr;
		},


		/**
		 * イベントを登録
		 */
		event: function () {
			/**
			 * チェックボックスのクリック時イベント
			 */
			$(document).on('click', '#wpa_admin_menus input', function () {
				adminMenuEditor.update();
			});

			$(document).on('click', '#wpa_admin_menus input[type="checkbox"]', function () {
				var menuid = $(this).next().next().data('menu-id');
				var checked = $(this).attr('checked');

				if (checked === 'checked') {
					$('#' + menuid).fadeOut();
				} else {
					$('#' + menuid).fadeIn();
				}

			});

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
				$('#' + menuid).find('.wp-menu-name').fadeOut().text(input_value).fadeIn();
				$(this).hide();
				$(this).prev().hide();
				$(this).prev().prev().show();
				adminMenuEditor.update();
			});

		}
	};

	/**
	 * ロード時のイベント
	 */
	$(function () {
		var settings = wpa_ADMIN_MENU || {};
		adminMenuEditor.init( settings );
		if ($('body').hasClass('toplevel_page_wpa_options_page')) {
			var order_data = $("#wpa_admin_menus").sortable({
				placeholder: "ui-state-highlight",
				update: function (event, ui) {
					$(this).find('.menu-list-item').each(function(key,item){
						$(item).attr( 'data-order', key );
						setTimeout(function(){
							adminMenuEditor.update();
						}, 500);
					});
				}
			});
		}
	});

})(jQuery);
