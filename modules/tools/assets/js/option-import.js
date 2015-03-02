;(function($){
    $(document).on('click', '#option-import',function(e){
        e.preventDefault();
        var _wp_import_nonce = $('#wp_import_nonce').val();
        $.ajax({
            type : 'post',
            data : {
                action : 'ggs_option_import',
                _wp_import_nonce : _wp_import_nonce
            },
            success : function(data){
                console.log(data);
            }
        })
    })

})(jQuery)
