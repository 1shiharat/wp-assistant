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

	var wpa = {
		timer: 0,
		submit: '#wpa-submit',
		// メッセージを表示
		message: function (type, message) {
			var messageContainer = $('.wpa-message-' + type);

			if (message) {
				messageContainer.html(message);
			}

			var already = 'message-aleady';
			clearTimeout(wpa.timer);
			messageContainer.show();
			messageContainer.addClass('slideInDown');
			if ( ! messageContainer.hasClass(already)) {
				wpa.timer = setTimeout(function () {
					messageContainer.addClass('slideOutUp');
					setTimeout(function () {
						messageContainer.removeClass('slideInDown');
						setTimeout(function () {
							messageContainer.removeClass('slideOutUp');
							messageContainer.hide();
						}, 1000);
					}, 1000);
				}, 1600);
			}
		},

		// 初期化
		init: function () {
			window.addEventListener("hashchange", wpa.changeOnHash, false);

			$('.acoordion').multiAccordion({
				animate: 100,
				autoHeight: false,
				heightStyle: "content"
			});

			// タブ
			$('#wpa_tabs').tabs({
				hide: {
					effect: "fadeOut",
					duration: 200
				},
				show: {
					effect: "fadeIn",
					duration: 200
				}
			}).addClass("ui-tabs-vertical ui-helper-clearfix");
			$('#wpa_tabs ul li a').on('click', function () {
				location.hash = $(this).attr('href');
				window.scrollTo(0, 0);
			});
			$('.form-group-radiobox').buttonset();

			$('#wpa-submit').attr('disabled', 'disabled');

			wpa.event();

			$('#wpa_tabs_hide').remove();
		},
		// 送信ボタンをクリックできるように
		submit_enhanced: function () {
			$(wpa.submit).removeAttr('disabled');
		},
		// ハッシュの変更を監視し、タブを変更
		changeOnHash: function () {
			var tab_id = location.hash;
			var tabIndexs = {
				'#wpa-basic-setting': 0,
				'#wpa-dashboard-setting': 1,
				'#wpa-admin-menu-setting': 2
			};
			$('#wpa_tabs').tabs(
				'enable', tab_id
			).addClass("ui-tabs-vertical ui-helper-clearfix");
		},
		// オプションを更新
		updateOption: function (e) {
			e.preventDefault();
			if (false === wpa.submit_flag) {
				return false;
			}
			wpa.submit_flag = false;
			var self = $(this);
			$('#wpa_tabs ul').find('.spinner').show();
			var formArray = $('#wpa_settings_form').serializeArray();
			_.each(formArray, function (form, key) {
				if (form.name.match(/wpa_supports_check/)) {
					delete formArray[key];
				}
			});

			$.ajax({
				'type': 'post',
				'url': ajaxurl,
				'data': {
					'action': wpaSETTINGS.action,
					'_wp_nonce': wpaSETTINGS._wp_nonce,
					'form': formArray
				},
				'success': function (data) {
					var status = data-0;
					if ( 1 === status ) {

						$('#wpa_tabs ul').find('.spinner').hide();
						wpa.message('success');
						self.attr('disabled', 'disabled');
					} else if (3 === status ) {
						$('#wpa_tabs ul').find('.spinner').hide();
						wpa.message('no-update');
						self.attr('disabled', 'disabled');
					} else {
						$('#wpa_tabs ul').find('.spinner').hide();
						wpa.message('faild');
					}
					wpa.submit_flag = true;
				}
			});
		},

		/**
		 * メディアアップローダーを開く
		 *
		 * @param self
		 * @param e
		 * @returns {boolean}
		 */
		openMediaUpload: function (self, e) {
			e.preventDefault();

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
				wpa.submit_enhanced();
				self.prev('.wpa-url').val(attachment.url);
			});
			file_frame.open();
		},

		/**
		 * イベントの実行
		 */
		event: function () {
			$(document).on('click', wpa.submit, function (e) {
				wpa.updateOption(e);
			});
			$(document).on('click', '.wpa-browse', function (e) {
				wpa.openMediaUpload($(this), e);
			});
			$(document).change(function () {
				wpa.submit_enhanced();
			});
		}
	};

	/**
	 * ロード時のイベント
	 */
	$(function (){
		window.wpa = wpa;
		wpa.init();
	});

})(jQuery);
