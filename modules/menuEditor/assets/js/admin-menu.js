(function ($) {
	"use strict";

	var adminMenuEditor = {
		insertTarget: '#wpa_admin_menus_list',
		saveHiddenInput: '#admin_menu_hidden',
		checkedMenus: '#wpa_admin_menus .adminmenu_hidden_check',
		menuListTemplateID: '#wpa_admin_menus_template',
		adminMenu: '#adminmenu li',
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
					parent = $(this).closest('.menu-list-item'),
					text = $(this).val().replace(/\s+/g, "+"),
					disp = ( parent.find('.adminmenu_hidden_check').attr('checked') ) ? 0 : 1,
					target = ( parent.find('.menu-list-item-target_input').val() ) ? parent.find('.menu-list-item-target_input').val() : '',
					link = adminMenuEditor.utf8_to_b64($(this).closest('.menu-list-item').find('.menu-list-item-link_input').val()),
					order = $(this).closest('.menu-list-item').data('order');
				if (text) {
					if (i === 0) {
						menuListTextStr += i + 'id=' + id;
						menuListTextStr += '&' + i + 'text=' + text;
						menuListTextStr += '&' + i + 'link=' + link;
						menuListTextStr += '&' + i + 'disp=' + disp;
						menuListTextStr += '&' + i + 'target=' + target;
						menuListTextStr += '&' + i + 'order=' + i;
					} else {
						menuListTextStr += '&' + i + 'id=' + id;
						menuListTextStr += '&' + i + 'text=' + text;
						menuListTextStr += '&' + i + 'link=' + link;
						menuListTextStr += '&' + i + 'disp=' + disp;
						menuListTextStr += '&' + i + 'target=' + target;
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
		 * @returns object
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

					if (param_key == "link") {
						if (d(pair[1].replace(/\+/g, " "))) {
							params[parseInt(num)][param_key] = adminMenuEditor.b64_to_utf8(d(pair[1].replace(/\+/g, " ")));
						} else {
							params[parseInt(num)][param_key] = d(pair[1].replace(/\+/g, " "));
						}
					} else if (typeof params[parseInt(num)] !== "undefined" && d(pair[1].replace(/\+/g, " "))) {
						params[parseInt(num)][param_key] = d(pair[1].replace(/\+/g, " "));
					}
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
			menuObj = _.sortBy(menuObj, function (menu) {
				return parseInt(menu.order);
			});
			return {
				menus: menuObj
			};
		},

		/**
		 * ユニークなID
		 * @param L
		 * @returns {string}
		 */
		randomstring: function (L) {
			var s = '';
			var randomchar = function () {
				var n = Math.floor(Math.random() * 62);
				if (n < 10) return n; //1-10
				if (n < 36) return String.fromCharCode(n + 55); //A-Z
				return String.fromCharCode(n + 61); //a-z
			};
			while (s.length < L) s += randomchar();
			return s;
		},

		/**
		 * 初期化
		 * @param wpa_ADMIN_MENU
		 */
		init: function (settings) {
			var self = adminMenuEditor;
			var defaults;
			var menus;
			adminMenuEditor.settings = settings;
			if ($(adminMenuEditor.menuListTemplateID).length) {
				self.setTemplate();
				adminMenuEditor.cloneMenu();
				defaults = adminMenuEditor.defaults(adminMenuEditor.originalAdminMenu);
				menus = ( settings.menus ) ? settings.menus : defaults;

				adminMenuEditor.enhanced(self.getData(menus));
				var compiledHtml = self.compiled(self.getData(menus));
				self.getTarget().html(compiledHtml);
				adminMenuEditor.update();
				adminMenuEditor.event();
			} else {
				defaults = adminMenuEditor.defaults();
				menus = settings.menus || defaults;
				adminMenuEditor.enhanced(self.getData(menus));
			}
			$("#admin-menu-hide-css").remove();
		},

		cloneMenu: function () {
			adminMenuEditor.originalAdminMenu = $('#adminmenu').clone();
			return adminMenuEditor.originalAdminMenu;
		},

		/**
		 * テンプレートをコンパイル
		 * @param menus
		 * @returns {*}
		 */
		compiled: function (menus) {
			return adminMenuEditor.template(menus);
		},

		/**
		 * 設定を有効化
		 * @param settings
		 */
		enhanced: function (settings) {
			var adminMenu = $('#adminmenu').clone();
			$('#adminmenu').attr('id', 'adminmenu_original').hide();
			$('#adminmenuwrap').append(adminMenu);


			_.each(settings.menus, function (menu, key) {
				if (!menu.id) {
					return false;
				}
				if (adminMenu.find('#' + menu.id).length == 0) {
					adminMenu.find('#collapse-menu').before('<li id="' + menu.id + '" class=" menu-top menu-icon-generic menu-top-first menu-top-last"><a href="" class=" menu-top menu-icon-generic menu-top-first menu-top-last"><div class="wp-menu-image dashicons-before dashicons-admin-generic"><br></div> <div class="wp-menu-name">' + menu.text + '</div></a></li>');
				}
				var target = adminMenu.find('#' + menu.id);
				if (typeof menu.link !== 'undefined') {
					target.children('a').attr('href', menu.link);
				}
				if (typeof menu.target !== 'undefined') {
					target.children('a').attr('target', menu.target);
				}
				if (adminMenuEditor.settings.user.userflag === true && menu.disp == 0) {
					target.remove();
				}
				target.find('.wp-menu-name').text(menu.text);
				target.attr('data-order', menu.order);
			});
			adminMenu.html(_.sortBy(adminMenu.children(), function (menu) {
				if (!$(menu).data('order') && $(menu).data('order') + '' !== "0") {
					return 100;
				}
				return parseInt($(menu).data('order'));
			}));
		},

		utf8_to_b64: function (str) {
			return window.btoa(unescape(encodeURIComponent(str)));
		},

		b64_to_utf8: function (str) {
			return decodeURIComponent(escape(window.atob(str)));
		},

		// デフォルトの値を取得
		defaults: function (originalAdminMenu) {
			var menuStr = '';
			var i = 0;

			var adminMenu = adminMenuEditor.originalAdminMenu || _.clone($(adminMenuEditor.adminMenu));
			adminMenu.children('li').each(function () {
				var id = $(this).attr('id');
				var menuName = $(this).find('.wp-menu-name');
				var target = '';
				var linktext = ( $(this).children('a').attr('href') ) ? $(this).children('a').attr('href') : '';
				var link = ( linktext ) ? adminMenuEditor.utf8_to_b64(linktext) : ' ';
				menuName.find('.pending-count').remove();
				menuName.find('.plugin-count').remove();
				var text = $(this).find('.wp-menu-name').text().replace(/(^\s+)|(\s+$)/g, "");
				if (text) {
					if (i === 0) {
						menuStr += i + 'id=' + id;
						menuStr += '&' + i + 'text=' + text;
						menuStr += '&' + i + 'link=' + link;
						menuStr += '&' + i + 'target=' + target;
						menuStr += '&' + i + 'disp=' + 1;
						menuStr += '&' + i + 'order=' + i;
					} else {
						menuStr += '&' + i + 'id=' + id;
						menuStr += '&' + i + 'link=' + link;
						menuStr += '&' + i + 'text=' + text;
						menuStr += '&' + i + 'disp=' + 1;
						menuStr += '&' + i + 'target=' + target;
						menuStr += '&' + i + 'order=' + i;
					}
				}
				i++;
			});
			return menuStr;
		},

		// ソートを有効化
		sortableInit: function () {
			$("#wpa_admin_menus").sortable({
				placeholder: "ui-state-highlight",
				update: function (event, ui) {
					$(this).find('.menu-list-item').each(function (key, item) {
						$(item).attr('data-order', key);
						setTimeout(function () {
							adminMenuEditor.update();
						}, 500);
					});
				}
			});
		},

		/**
		 * デフォルトのメニューを復元
		 */
		restoreDefaultMenu: function () {
			var defaults = adminMenuEditor.defaults(adminMenuEditor.originalAdminMenu);
			$(adminMenuEditor.saveHiddenInput).val(defaults);
			var compiledHtml = adminMenuEditor.compiled(adminMenuEditor.getData(defaults));
			adminMenuEditor.getTarget().html(compiledHtml);
		},

		removeDisable: function () {
			$('#wpa-submit').removeAttr('disabled');
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

			// リセット
			$(document).on('click', '#wpa_admin_menu_reset', function (e) {
				e.preventDefault();
				var template = _.template($('#wpa_admin_menu_dialog').html());
				var template_html = template({
					title: adminMenuEditor.settings.dialog.title,
					context: adminMenuEditor.settings.dialog.context
				});

				$(adminMenuEditor.menuListTemplateID).after(template_html);

				$("#admin_menu_dialog").dialog({
					resizable: false,
					width: 500,
					height: 440,
					modal: true,
					buttons: {
						OK: function () {
							adminMenuEditor.restoreDefaultMenu();
							adminMenuEditor.sortableInit();
							$(this).dialog("close");
						},
						Cancel: function () {
							$(this).dialog("close");
						}
					}
				});
				adminMenuEditor.removeDisable();

			});

			// チェックボックスをクリックした時のイベント
			$(document).on('click', '#wpa_admin_menus input[type="checkbox"]', function () {
				var menuid = $(this).next().next().data('menu-id');
				var checked = $(this).attr('checked');
				if (checked === 'checked') {
					$('#' + menuid).fadeOut();
				} else {
					$('#' + menuid).fadeIn();
				}
				adminMenuEditor.removeDisable();

			});

			/**
			 * メニュー項目をクリックした時のイベント
			 */
			$(document).on('click', '.menu-list-item-text', function (e) {
				var parentList = $(this).closest('.menu-list-item');
				var input = parentList.find('.menu-list-item-text_input,.menu-list-item-link_input');
				if (!parentList.hasClass('on')) {
					parentList.addClass('on');
					parentList.find('.wpa_input-group').removeClass('hide');
					//$(this).hide();
					input.show();
					parentList.find('button').show();
				} else if ($(this).next(".menu-list-item-text_input").attr('class') !== $(e.target).attr('class')) {
					parentList.removeClass('on');
					parentList.find('.wpa_input-group').addClass('hide');
					$(this).text(input.val());
					$(this).show();
					
					parentList.find('button').hide();
					input.hide();
					adminMenuEditor.removeDisable();
				}

			});

			$(document).on('click', '.menu-list-item-remove', function (e) {
				e.preventDefault();
				var parent = $(this).closest('.menu-list-item');
				parent.fadeOut('500');
				setTimeout(function () {
					parent.remove();
				}, 500);

			});

			/**
			 * セーブボタンをクリックした時のイベント
			 */
			$(document).on('click', '.menu-list-item-text-save', function (e) {
				e.preventDefault();
				var parent = $(this).closest('.menu-list-item');
				var input_value = parent.find('.menu-list-item-text_input').val();
				parent.find('.menu-list-item-text').fadeOut().text(input_value).fadeIn();
				parent.find('.wpa_input-group').addClass('hide');
				var menuid = $(this).prev().data('menu-id');
				$('#' + menuid).find('.wp-menu-name').fadeOut().text(input_value).fadeIn();
				$(this).hide();
				parent.removeClass('on');
				adminMenuEditor.update();
				adminMenuEditor.removeDisable();
			});

			// 新しいメニューを追加
			$(document).on('click', '#add_menu_list_item', function (e) {
				e.preventDefault();
				var newMenuItem = _.template($('#wpa_admin_menu_template').html());
				var compiledHtml = newMenuItem({
					menu: {
						order: 20,
						text: ' ',
						id: adminMenuEditor.randomstring(8),
						disp: 1
					}
				});
				$(adminMenuEditor.insertTarget).append(compiledHtml);
			});

			// メニューラベルに変更をリアルタイムに反映
			$('.menu-list-item-text_input').on('keydown keyup keypress change', function () {
				$(this).closest('.menu-list-item').find('.menu-list-item-text').text($(this).val());
			});

			// 複数ユーザー選択時のリセット
			$(document).on('click','#wpa_menu_user_reset',function(e){
				e.preventDefault();
				$('#admin_menu_user option').removeAttr('selected');
				adminMenuEditor.removeDisable();
			});


		}
	};

	/**
	 * ロード時のイベント
	 */
	$(function () {
		if (typeof wpa_ADMIN_MENU == 'undefined') {
			return false;
		}
		var settings = wpa_ADMIN_MENU || {};

		window.wpa.adminMenuEditor = adminMenuEditor;
		wpa.adminMenuEditor.init(settings);
		if ($('body').hasClass('toplevel_page_wpa_options_page')) {
			$("#wpa_admin_menus").sortable({
				placeholder: "ui-state-highlight",
				update: function (event, ui) {
					$(this).find('.menu-list-item').each(function (key, item) {
						$(item).attr('data-order', key);

						setTimeout(function () {
							adminMenuEditor.update();
						}, 500);
					});
					adminMenuEditor.removeDisable();
				}
			});
		}
	});

})(jQuery);
