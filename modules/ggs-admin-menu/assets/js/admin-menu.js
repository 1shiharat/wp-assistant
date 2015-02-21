;

"use strict";

(function($){
    /**
     *
     * @param adminMenu
     * @returns {*}
     */
    function getMenuArray( adminMenu ) {
        var menus = {};

        if ( typeof adminMenu == 'undefined' ){
            return false;
        }

        $.each( adminMenu, function( menuKey, menu ){

            var menuName = $(this).find(".wp-menu-name").text();
            var menuID = $(this).attr('id');
            menus[menuKey] = {
                menuName: menuName,
                menuID: menuID,
            };

        } );

        return menus;
    }

    /**
     * チェックされているidを更新する
     *
     */
    function changeCheckedInput(){
        var checkedMenus = $('#ggs_admin_menus input');
        var checkedMenuID = [];

        $.each( checkedMenus , function( key, checkedMenu ){

            if ( $(this).attr('checked') == 'checked' ) {
                checkedMenuID[key] = $(this).val();
            }
        } );
        ;
        $('#ggsupports_admin_menu_hidden').val( checkedMenuID.join(',') );
    }


    // ロード時のイベント
    $(function(){
        var menuInputRender = $('#ggs_admin_menus');
        var adminMenu = $('#adminmenu > li');
        var menus = getMenuArray( adminMenu );
        var savedMenus = GGS_ADMIN_MENU.menus.split(',');
        $.each( menus, function(){
            if ( this.menuID && this.menuName ) {
                var checked = '';
                if ( $.inArray( this.menuID, savedMenus ) > 0 ){
                    checked = ' checked="checked"';
                }
                menuInputRender.append('<p><input type="checkbox" name="ggsupports_checkobox[]" value="' + this.menuID + '" ' + checked + '/>' + this.menuName + '</p>');
            }
        });

        changeCheckedInput();

    });

    $(document).on( 'click', '#ggs_admin_menus input', function(){
        changeCheckedInput();
    })


})(jQuery);
