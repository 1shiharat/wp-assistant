<?php
/**
 * ソースコードエディタ
 */
namespace WP_Assistant\inc\fields;

use WP_Assistant\inc\config;

class source extends field {

	public $id = '';
	public $value = '';
	public $field = '';

	/**
	 * 初期化
	 *
	 * @param $field
	 */
	public function __construct( $field ) {
		$this->field = $field;
		$this->set();

		$this->options = wp_parse_args( $this->options, array(
				'mode' => 'php',
				'width' => '100%',
				'height' => '500px',
			)
		);
		$this->render();
	}

	public function render() {
		$ace_editor_id = $this->get( 'id' ) . '_ace';
		?>
		<textarea class="wpa-url aceeditor" style="width: <?php echo $this->options['width']; ?>; height: <?php echo $this->options['height']; ?>;" data-lang="css" name="<?php echo $this->get( 'id' ); ?>" id="<?php echo $this->get( 'id' ); ?>" cols="30" rows="10"><?php echo stripslashes_deep( $this->value ) ?></textarea>
		<input type="button" class="button wpa-browse button-small" value="<?php _e( 'Upload File', 'wp-assistant' ); ?>" />
		<div id="<?php echo $this->get( 'id' ); ?>_ace"></div>
		<script>
;(function($){
	function aceEditorInit(){

		ace.require("ace/ext/language_tools");
		ace.require("ace/ext/emmet");
		var editor = ace.edit("<?php echo $ace_editor_id;?>");

		editor.setTheme("ace/theme/github");
		editor.getSession().setMode("ace/mode/<?php echo $this->options['mode']; ?>" );
		editor.setOption({
			enableEmmet: true,
			enableBasicAutocompletion: true,
			enableSnippets: true,
			enableLiveAutocompletion: false
		});
		var textarea = $('#<?php echo $this->get( 'id' ); ?>').hide();
		editor.getSession().setValue(textarea.val());
		editor.getSession().on('change', function(){
			textarea.val(editor.getSession().getValue());
			$('#wpa-submit').removeAttr('disabled');
		});
		textarea.on('change', function(){
			editor.getSession().setValue(textarea.val());

		});
	}

	$(function(){

		var textarea = $('#<?php echo $this->get( 'id' ); ?>');
		var aceEditor = $('#<?php echo $ace_editor_id ; ?>');
		if ( textarea.width() < 600 ){
			aceEditor.width('600');
		} else {
			aceEditor.width(textarea.width());
		}
		aceEditor.height(textarea.height()+200);
		aceEditorInit();
	});
})(jQuery);
		</script>
		<?php
	}
}
