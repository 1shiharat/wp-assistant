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
