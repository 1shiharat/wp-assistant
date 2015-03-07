(function ($) {
    "use strict";
    /**
     * アコーディオンをすべて開く
     */
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
        ).addClass( "ui-tabs-vertical ui-helper-clearfix" );
    }

    $(function () {
        window.addEventListener("hashchange", changeOnHash, false);
        $('.acoordion').multiAccordion({
            animate: 100,
            autoHeight: false,
            heightStyle: "content"
        });
        $('#wpa_tabs').tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
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

})(jQuery);
;(function($){
    "use strict";
    $(function () {
        var welcomeDashboard = $( '#wpadashboard' );
        $( '#dashboard-widgets-wrap' ).prepend( welcomeDashboard );
        $( '#wpa_dashboard_widget' ).remove();
    });
})(jQuery);


/* ================================================================ *
 ajaxzip3.js ---- AjaxZip3 郵便番号→住所変換ライブラリ

 Copyright (c) 2008 Ninkigumi Co.,Ltd.
 http://code.google.com/p/ajaxzip3/

 Copyright (c) 2006-2007 Kawasaki Yusuke <u-suke [at] kawa.net>
 http://www.kawa.net/works/ajax/AjaxZip2/AjaxZip2.html

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 * ================================================================ */

AjaxZip3=function(){};AjaxZip3.VERSION="0.4";AjaxZip3.JSONDATA="http://ajaxzip3.googlecode.com/svn/trunk/ajaxzip3/zipdata";AjaxZip3.CACHE=[];AjaxZip3.prev="";AjaxZip3.nzip="";AjaxZip3.fzip1="";AjaxZip3.fzip2="";AjaxZip3.fpref="";AjaxZip3.addr="";AjaxZip3.fstrt="";AjaxZip3.farea="";AjaxZip3.PREFMAP=[null,"北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県","新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県","滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県","徳島県","香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県"];AjaxZip3.zip2addr=function(h,g,k,b,a,l){AjaxZip3.fzip1=AjaxZip3.getElementByName(h);AjaxZip3.fzip2=AjaxZip3.getElementByName(g,AjaxZip3.fzip1);AjaxZip3.fpref=AjaxZip3.getElementByName(k,AjaxZip3.fzip1);AjaxZip3.faddr=AjaxZip3.getElementByName(b,AjaxZip3.fzip1);AjaxZip3.fstrt=AjaxZip3.getElementByName(a,AjaxZip3.fzip1);AjaxZip3.farea=AjaxZip3.getElementByName(l,AjaxZip3.fzip1);if(!AjaxZip3.fzip1){return}if(!AjaxZip3.fpref){return}if(!AjaxZip3.faddr){return}var c=AjaxZip3.fzip1.value;if(AjaxZip3.fzip2&&AjaxZip3.fzip2.value){c+=AjaxZip3.fzip2.value}if(!c){return}AjaxZip3.nzip="";for(var f=0;f<c.length;f++){var d=c.charCodeAt(f);if(d<48){continue}if(d>57){continue}AjaxZip3.nzip+=c.charAt(f)}if(AjaxZip3.nzip.length<7){return}var j=function(){var i=AjaxZip3.nzip+AjaxZip3.fzip1.name+AjaxZip3.fpref.name+AjaxZip3.faddr.name;if(AjaxZip3.fzip1.form){i+=AjaxZip3.fzip1.form.id+AjaxZip3.fzip1.form.name+AjaxZip3.fzip1.form.action}if(AjaxZip3.fzip2){i+=AjaxZip3.fzip2.name}if(AjaxZip3.fstrt){i+=AjaxZip3.fstrt.name}if(i==AjaxZip3.prev){return}AjaxZip3.prev=i};var m=AjaxZip3.nzip.substr(0,3);var e=AjaxZip3.CACHE[m];if(e){return AjaxZip3.callback(e)}AjaxZip3.zipjsonpquery()};AjaxZip3.callback=function(g){var l=g[AjaxZip3.nzip];var d=(AjaxZip3.nzip-0+4278190080)+"";if(!l&&g[d]){l=g[d]}if(!l){return}var b=l[0];if(!b){return}var n=AjaxZip3.PREFMAP[b];if(!n){return}var c=l[1];if(!c){c=""}var q=l[2];if(!q){q=""}var e=l[3];if(!e){e=""}var p=AjaxZip3.faddr;var j=c;if(AjaxZip3.fpref.type=="select-one"||AjaxZip3.fpref.type=="select-multiple"){var a=AjaxZip3.fpref.options;for(var f=0;f<a.length;f++){var m=a[f].value;var o=a[f].text;a[f].selected=(m==b||m==n||o==n)}}else{if(AjaxZip3.fpref.name==AjaxZip3.faddr.name){j=n+j}else{AjaxZip3.fpref.value=n}}if(AjaxZip3.farea){p=AjaxZip3.farea;AjaxZip3.farea.value=q}else{j+=q}if(AjaxZip3.fstrt){p=AjaxZip3.fstrt;if(AjaxZip3.faddr.name==AjaxZip3.fstrt.name){j=j+e}else{if(e){AjaxZip3.fstrt.value=e}}}AjaxZip3.faddr.value=j;if(!p){return}if(!p.value){return}var k=p.value.length;p.focus();if(p.createTextRange){var h=p.createTextRange();h.move("character",k);h.select()}else{if(p.setSelectionRange){p.setSelectionRange(k,k)}}};AjaxZip3.getResponseText=function(b){var c=b.responseText;if(navigator.appVersion.indexOf("KHTML")>-1){var a=escape(c);if(a.indexOf("%u")<0&&a.indexOf("%")>-1){c=decodeURIComponent(a)}}return c};AjaxZip3.getElementByName=function(d,b){if(typeof(d)=="string"){var e=document.getElementsByName(d);if(!e){return null}if(e.length>1&&b&&b.form){var c=b.form.elements;for(var a=0;a<c.length;a++){if(c[a].name==d){return c[a]}}}else{return e[0]}}return d};AjaxZip3.zipjsonpquery=function(){var a=AjaxZip3.JSONDATA+"/zip-"+AjaxZip3.nzip.substr(0,3)+".js";var b=document.createElement("script");b.setAttribute("type","text/javascript");b.setAttribute("src",a);b.setAttribute("charset","UTF-8");document.getElementsByTagName("head").item(0).appendChild(b)};function zipdata(a){AjaxZip3.callback(a)};

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
