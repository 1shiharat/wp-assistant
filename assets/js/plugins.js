(function ($) {
    "use strict";
    $.fn.multiAccordion = function() {
        $(this).addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
            .find("h3")
            .addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom")
            .hover(function() { $(this).toggleClass("ui-state-hover"); })
            .prepend('<span class="ui-icon ui-icon-triangle-1-e"></span>')
            .click(function() {
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

    function changeOnHash() {
        var tab_id = location.hash;
        var tabIndexs = {
            '#wpa-basic-setting': 0,
            '#wpa-dashboard-setting': 1,
            '#wpa-admin-menu-setting': 2
        };
        $('#wpa_tabs').tabs(
            'enable', tab_id
        );
    }

    $(function () {
        window.addEventListener("hashchange", changeOnHash, false);
        $('.acoordion').multiAccordion({
            animate: 100,
            autoHeight: false,
            heightStyle: "content"
        });
        $('#wpa_tabs').tabs();
        $('#wpa_tabs ul li a').on('click', function () {
            location.hash = $(this).attr('href');
            window.scrollTo(0, 0);
        });
        $('.form-group-radiobox').buttonset();
        $('#submit').attr('disabled', 'disabled');

    });

    $(document).change('#wpa_settings_form *', function () {
        $('#submit').removeAttr('disabled');
    });

    var submit_flag = true;

    /**
     * 変更を保存時のイベント
     * @return void
     */
    $(document).on('click', '#submit', function (e) {
        e.preventDefault();
        if (false === submit_flag) {
            return false;
        }
        submit_flag = false;
        $('#wpa_tabs ul').find('.spinner').show();
        $.ajax({
            'type': 'post',
            'url': ajaxurl,
            'data': {
                'action': wpaSETTINGS.action,
                '_wp_nonce': wpaSETTINGS._wp_nonce,
                'form': $('#wpa_settings_form').serialize(),
            },
            'success': function (data) {
                if (1 == data) {
                    $('#wpa_tabs ul').find('.spinner').hide();
                    wpaMessage('success');
                    $('#submit').attr('disabled', 'disabled');
                } else {
                    $('#wpa_tabs ul').find('.spinner').hide();
                    wpaMessage('faild');
                }
                submit_flag = true;
            }
        });
    });
    function countReset( target ){
        if ( ! target ){
            return false;
        }
        target = $('.post-count-' + target);
        target.text('0');
    }

    var optimize_flag = true;
    /**
     * 最適化の実行
     */
    $(document).on('click', '#optimize_submit', function (e) {
        e.preventDefault();
        if ( false === optimize_flag) {
            return false;
        }
        optimize_flag = false;
        $('.run_optimize').find('.spinner').show();
        var nonce = $('#optimize_nonce').val();
        $.ajax({
            'type': 'post',
            'url': ajaxurl,
            'data': {
                'action': 'run_optimize',
                '_wp_optimize_nonce': nonce
            },
            'success': function (data) {
                $('.run_optimize').find('.spinner').hide();
                if ( data.status == 'faild') {

                    wpaMessage('faild', '<h3>' + data.html + '</h3>');
                    //return false;
                } else {

                    //var message = '';
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

                    optimize_flag = true;
                }
            }
        });

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
                menuID: menuID,
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
                menuInputRender.append('<p><input type="checkbox" name="wpaupports_checkobox[]" value="' + this.menuID + '" ' + checked + '/>' + this.menuName + '</p>');
            }
        });
        changeCheckedInput();
    });

    // チェックボックスのクリック時イベント
    $(document).on('click', '#wpa_admin_menus input', function () {
        changeCheckedInput();
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
