

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

			$('.wpa input[type=radio]').each(function(){
				var self = $(this),
					label = self.next(),
					label_text = label.text();
				label.remove();
				self.iCheck({
					checkboxClass: 'icheckbox_line-aero',
					radioClass: 'iradio_line-aero',
					activeClass: 'active',
					checkedClass: 'animated flash checked',
					insert: '<div class="icheck_line-icon"></div>' + label_text
				});
			});

			setTimeout(function(){
				$('.wpa input[type=checkbox]').iCheck({
					checkboxClass: 'icheckbox_square-aero',
					activeClass: 'active',
					checkedClass: 'animated fadeIn checked'
				});
			}, 1000);

			$('.wpa input[type=text],.wpa input[type=url],.wpa select').addClass('form-control');
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
