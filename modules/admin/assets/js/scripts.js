;(function ($) {
    /**
     * メッセージを表示
     * @param type メッセージの種類
     */
    var ggsMessage = function (type) {
        var messageContainer = $('.ggs-message-' + type);
        var already = 'message-aleady';
        messageContainer.fadeIn();
        if ( ! messageContainer.hasClass(already)) {
            setTimeout(function () {
                messageContainer.fadeOut();
            }, 1500);
        }
        messageContainer.addClass(already);
    }

    function changeOnHash(){
        var tab_id = location.hash;
        var tabIndexs = {
            '#ggs-basic-setting'  : 0,
            '#ggs-dashboard-setting': 1,
            '#ggs-admin-menu-setting' : 2
        };
        $('#ggs-tabs').tabs(
            'enable', tab_id
        );
    }


    $(function () {
        window.addEventListener("hashchange", changeOnHash, false);
        $('.acoordion').accordion({animate: 100, autoHeight: false,heightStyle:"content"});
        $('#ggs-tabs').tabs();
        $('#ggs-tabs ul li a').on( 'click', function () {
            location.hash = $(this).attr('href');
            window.scrollTo(0, 0);
        });
        $('.form-group-radiobox').buttonset();
    });

    var flag = true;
    /**
     * 変更を保存時のイベント
     * @return void
     */
    $(document).on( 'click', '#submit', function(e){
        e.preventDefault();
        if ( false == flag ){
            return false;
        }
        flag = false;
        $('#ggs-tabs').find( '.spinner').show();
        console.log($('#ggs_settings_form').serialize());
        $.ajax({
            'type': 'post',
            'url' : ajaxurl,
            'data' :{
                'action' : GGSSETTINGS.action,
                '_wp_nonce' : GGSSETTINGS._wp_nonce,
                'form': $('#ggs_settings_form').serialize(),
            },
            'success' : function(data){
                if ( 1 == data ) {
                    $('#ggs-tabs').find('.spinner').hide();
                    ggsMessage('success');
                } else {
                    $('#ggs-tabs').find('.spinner').hide();
                    ggsMessage('faild');
                }
                flag = true;
            }
        })
    });
})(jQuery);