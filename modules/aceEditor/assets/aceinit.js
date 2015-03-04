;(function($){
    function aceEditorInit(){

        ace.require("ace/ext/language_tools");
        ace.require("ace/ext/emmet");
        var editor = ace.edit("aceeditor");

        editor.setTheme("ace/theme/github");
        editor.getSession().setMode("ace/mode/" + Ace.mode );
        editor.setOption({
            enableEmmet: true,
            enableBasicAutocompletion: true,
            enableSnippets: true,
            enableLiveAutocompletion: false
        });
        var textarea = $('#newcontent').hide();
        editor.getSession().setValue(textarea.val());
        editor.getSession().on('change', function(){
            textarea.val(editor.getSession().getValue());
        });
        textarea.on('change', function(){
            editor.getSession().setValue(textarea.val());
        });
    }

    $(function(){

        var textarea = $('#newcontent');
        $('#newcontent').closest( 'div').append('<div id="aceeditor"></div>');
        var aceEditor = $('#aceeditor');
        aceEditor.width(textarea.width());
        aceEditor.height(textarea.height()+200);
        aceEditorInit();
    });

})(jQuery);