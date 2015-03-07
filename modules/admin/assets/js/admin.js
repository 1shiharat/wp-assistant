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