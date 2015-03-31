(function ($) {
	"use strict";
	/**
	 * アコーディオンをすべて開く
	 */
	$.fn.multiAccordion = function () {
		$(this).addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
			.find("h3")
			.addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom")
			.hover(function () {
				$(this).toggleClass("ui-state-hover");
			})
			.prepend('<span class="ui-icon ui-icon-triangle-1-e"></span>')
			.click(function () {
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

	var submit = '#wpa-submit';

	function wpaMessage(type, message) {
		var messageContainer = $('.wpa-message-' + type);
		if (message) {
			messageContainer.html(message);
		}
		var already = 'message-aleady';
		messageContainer.fadeIn();
		clearTimeout(timer);
		if (!messageContainer.hasClass(already)) {
			timer = setTimeout(function () {
				messageContainer.fadeOut('500');
			}, 800);
		}
	}

	function submit_enhanced() {
		$(submit).removeAttr('disabled');
	}

	function changeOnHash() {
		var tab_id = location.hash;
		var tabIndexs = {
			'#wpa-basic-setting': 0,
			'#wpa-dashboard-setting': 1,
			'#wpa-admin-menu-setting': 2
		};
		$('#wpa_tabs').tabs(
			'enable', tab_id
		).addClass("ui-tabs-vertical ui-helper-clearfix");
	}


	$(document).change('#wpa_settings_form *', function () {
		submit_enhanced();
	});

	var submit_flag = true;

	/**
	 * 変更を保存時のイベント
	 * @return void
	 */
	$(document).on('click', submit, function (e) {
		e.preventDefault();
		if (false === submit_flag) {
			return false;
		}
		submit_flag = false;
		var self = $(this);
		$('#wpa_tabs ul').find('.spinner').show();
		$.ajax({
			'type': 'post',
			'url': ajaxurl,
			'data': {
				'action': wpaSETTINGS.action,
				'_wp_nonce': wpaSETTINGS._wp_nonce,
				'form': $('#wpa_settings_form').serializeArray()
			},
			'success': function (data) {
				if (1 == data) {
					$('#wpa_tabs ul').find('.spinner').hide();
					wpaMessage('success');
					self.attr('disabled', 'disabled');
				} else {
					$('#wpa_tabs ul').find('.spinner').hide();
					wpaMessage('faild');
				}
				submit_flag = true;
			}
		});
	});


	/**
	 * wp.media api を利用してメディアアップローダーを開く
	 */
	$(document).on('click', '.wpa-browse', function (event) {
		event.preventDefault();
		var self = $(this);
		var file_frame = null;

		if (file_frame) {
			file_frame.open();
			return false;
		}
		file_frame = wp.media.frames.file_frame = wp.media({
			title: self.data('uploader_title'),
			button: {
				text: self.data('uploader_button_text')
			},
			multiple: false
		});
		file_frame.on('select', function () {
			var attachment = file_frame.state().get('selection').first().toJSON();
			submit_enhanced();
			self.prev('.wpa-url').val(attachment.url);
		});
		file_frame.open();
	});

	/**
	 * ロード時のイベント
	 */
	$(function () {
		window.addEventListener("hashchange", changeOnHash, false);
		$('.acoordion').multiAccordion({
			animate: 100,
			autoHeight: false,
			heightStyle: "content"
		});

		// タブ
		$('#wpa_tabs').tabs().addClass("ui-tabs-vertical ui-helper-clearfix");


		$('#wpa_tabs ul li a').on('click', function () {
			location.hash = $(this).attr('href');
			window.scrollTo(0, 0);
		});

		$('.form-group-radiobox').buttonset();

		$('#wpa-submit').attr('disabled', 'disabled');

	});

})(jQuery);
