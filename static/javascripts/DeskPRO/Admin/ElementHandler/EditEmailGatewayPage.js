Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.EditEmailGatewayPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$(document).on('click', '.test-account-settings', function() {
			self.overlay.open();
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#test_settings_overlay'),
			onBeforeOverlayOpened: function() {
				var el = self.overlay.getElement();

				$('.result', el).hide().removeClass('loading');
				$('.success', el).hide();
				$('.error', el).hide();

				var postData = $('#gateway_form').serializeArray();
				self.testPostData = postData;
			}
		});

		$('button.test-trigger', '#test_settings_overlay').on('click', function() {
			var el = $('#test_settings_overlay');
			$('.result', el).show().addClass('loading');

			var postData = self.testPostData;

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
