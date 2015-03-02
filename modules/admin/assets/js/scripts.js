;(function ($) {
    /**
     * メッセージを表示
     * @param type メッセージの種類
     */
    var ggsMessage = function (type, message) {
        var messageContainer = $('.ggs-message-' + type);
        if ( message ){
            messageContainer.html( message );
        }

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
        $('#ggs_tabs').tabs(
            'enable', tab_id
        );
    }


    $(function () {
        window.addEventListener("hashchange", changeOnHash, false);
        $('.acoordion').accordion({animate: 100, autoHeight: false,heightStyle:"content"});
        $('#ggs_tabs').tabs();
        $('#ggs_tabs ul li a').on( 'click', function () {
            location.hash = $(this).attr('href');
            window.scrollTo(0, 0);
        });
        $('.form-group-radiobox').buttonset();
        $('#submit').attr( 'disabled', 'disabled' );

    });

    $(document).change('#ggs_settings_form *',function(){
        console.log('ishihar');
        $('#submit').removeAttr( 'disabled' );
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
        $('#ggs_tabs').find( '.spinner').show();
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
                    $('#submit').attr( 'disabled', 'disabled' );
                } else {
                    $('#ggs-tabs').find('.spinner').hide();
                    ggsMessage('faild');
                }
                flag = true;
            }
        })
    });

    $(document).on('click','#optimize_submit',function(e){
        e.preventDefault();
        if ( false == flag ){
            return false;
        }
        flag = false;
        $('.run_optimize').find( '.spinner').show();
        var nonce = $('#optimize_nonce').val();
        $.ajax({
            'type': 'post',
            'url' : ajaxurl,
            'data' :{
                'action' : 'run_optimize',
                '_wp_optimize_nonce' : nonce,
            },
            'success' : function(data){
                if ( data.status == 'faild' ){
                    $('.run_optimize').find('.spinner').hide();
                    ggsMessage('faild',data.html );
                    return false;
                }

                if ( data.optimize_revision ) {
                    ggsMessage('success',data.optimize_revision );
                }

                if( data.optimize_auto_draft ) {
                    ggsMessage('success',data.optimize_auto_draft );
                }

                if( data.optimize_trash ) {
                    ggsMessage('success',data.optimize_trash );
                }

                flag = true;
            }
        });

    })

})(jQuery);