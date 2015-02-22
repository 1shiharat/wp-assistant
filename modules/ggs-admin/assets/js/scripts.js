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

    $(function () {
        $('#ggs-tabs').tabs();
        $('.form-group-radiobox').buttonset();
        $('.acoordion').accordion({animate: 100});
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
        $.ajax({
            'type': 'post',
            'url' : ajaxurl,
            'data' :{
                'action' : GGSSETTINGS.action,
                '_wp_nonce' : GGSSETTINGS._wp_nonce,
                'form': $('#ggsupports_settings_form').serialize(),
            },
            'success' : function(data){
                $('#ggs-tabs').find( '.spinner').hide();
                ggsMessage( 'success' );
                flag = true;
            }
        })
    });
})(jQuery);