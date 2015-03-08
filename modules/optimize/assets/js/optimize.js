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

                }
                optimize_flag = true;
            }
        });

    });
})(jQuery);