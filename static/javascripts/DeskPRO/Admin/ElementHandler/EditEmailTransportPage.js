Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.EditEmailTransportPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$(document).on('click', ':radio.transport-type-backup', function() {
			if ($(this).is('.none')) {
				$('.test-account-settings.backup').hide();
			} else {
				$('.test-account-settings.backup').show();
			}
		});

		$(document).on('click', '.test-account-settings', function() {
			self.mode = '';
			if ($(this).is('.backup')) {
				self.mode = 'backup';
			}

			self.overlay.open();
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#test_settings_overlay'),
			onBeforeOverlayOpened: function() {
				var el = self.overlay.getElement();

				$('.result', el).hide().removeClass('loading');
				$('.success', el).hide();
				$('.error', el).hide();

				var postData = $('#transport_form').serializeArray();
				if (self.mode == 'backup') {
					postData.push({name: 'backup', value: 1});
				}

				var type = $('.email-address-type:checked').val();
				var setmail = false;
				if (type == 'exact') {
					setmail = $('.email-address-pattern').val();
				} else if (type == 'domain') {
					setmail = 'test@' + $('.email-domain-pattern').val();
				}

				if (setmail) {
					$('#test_send_from').val(setmail);
				}

				self.testPostData = postData;
			}
		});

		$('button.test-trigger', '#test_settings_overlay').on('click', function() {
			var el = $('#test_settings_overlay');
			$('.result', el).show().addClass('loading');

			var postData = self.testPostData;
			postData.push({name: 'send_to', value: $('input[name="send_to"]', el).val()});
			postData.push({name: 'send_from', value: $('input[name="send_from"]', el).val()});

			$.ajax({
				url: $(this).data('url'),
				type: 'POST',
				data: postData,
				dataType: 'json',
				complete: function() {
					$('.result', el).removeClass('loading');
				},
				success: function(data) {
					if (data.success) {
						$('.success', el).show();
					} else {
						$('.error', el).show();
						$('.error-msg', el).text(data.error_code + ' ' + data.error_message);
					}
				}
			});
		});
	}
});
