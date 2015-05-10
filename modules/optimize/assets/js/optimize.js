;(function ($) {
	var timer;
	var optimize = {
		timer: null,
		init: function () {
			this.optimize_flag = true;
			this.timer = 0;
			optimize.event();
		},
		// メッセージを表示
		message: function (type, message) {
			var messageContainer = $('.wpa-message-' + type);
			if (message) {
				messageContainer.html(message);
			}
			var already = 'message-aleady';
			//messageContainer.fadeIn();
			messageContainer.show();
			messageContainer.addClass('slideInDown');
			clearTimeout(optimize.timer);
			if (!messageContainer.hasClass(already)) {
				optimize.timer = setTimeout(function () {
					messageContainer.addClass('slideOutUp');
					setTimeout(function () {
						messageContainer.removeClass('slideInDown');
						setTimeout(function () {
							messageContainer.removeClass('slideOutUp');
							messageContainer.hide();
						}, 1000);
					}, 1000);
					//messageContainer.fadeOut('500');
				}, 1600);
			}
		},
		// カウントのリセット
		countReset: function (target) {
			if (!target) {
				return false;
			}
			target = $('.post-count-' + target);
			target.text('0');
		},
		// 最適化の実行
		optimizeSubmit: function (e) {
			e.preventDefault();
			if (false === this.optimize_flag) {
				return false;
			}

			var optimize_wrap = $('#wpa_optimize');
			var optimize_value = {
				revision: $('input[name="optimize_revision"]:checked').val(),
				auto_draft: $('input[name="optimize_auto_draft"]:checked').val(),
				trash: $('input[name="optimize_trash"]:checked').val()
			};


			this.optimize_flag = false;
			var nonce = $('#optimize_nonce').val();
			$('.run_optimize').find('.spinner').show();
			$.ajax({
				'type': 'post',
				'url': ajaxurl,
				'data': {
					'action': 'run_optimize',
					'_wp_optimize_nonce': nonce,
					'selected_action': optimize_value
				},
				'success': function (data) {
					$('.run_optimize').find('.spinner').hide();
					if (data.status == 'faild') {
						optimize.message('faild', '<h3>' + data.html + '</h3>');
					} else {
						var message = document.createElement('div');
						var heading = document.createElement('h3');

						if (data.revision) {
							$(message).append($(heading).text($(heading).text() + data.revision));
							optimize.countReset('revision');
						}

						if (data.auto_draft) {
							$(message).append($(heading).text($(heading).text() + data.auto_draft));
							optimize.countReset('auto_draft');
						}

						if (data.trash) {
							$(message).append($(heading).text($(heading).text() + data.trash));
							optimize.countReset('trash');
						}
						if ($(message).length > 0) {
							optimize.message('optimize', $(message));
						}

					}
					this.optimize_flag = true;
				}
			});
		},
		event: function () {
			$(document).on('click', '#optimize_submit', function (e) {
				optimize.optimizeSubmit(e);
			});
		}
	};

	$(function () {
		window.wpa.optimize = optimize;
		wpa.optimize.init();
	});

})(jQuery);
