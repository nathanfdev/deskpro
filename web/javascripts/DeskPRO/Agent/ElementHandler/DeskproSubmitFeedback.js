Orb.createNamespace('DeskPRO.Agent.ElementHandler');

/**
 * Any wrapper that has 'nav ul' for tabs. The wrapper acts
 * as the context for data-tab-for
 */
DeskPRO.Agent.ElementHandler.DeskproSubmitFeedback = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		window.SEND_FEEDBACK_WINDOW = this;

		$('#submit_beta_feedback').on('click', function(ev) {
			ev.preventDefault();
			self.open();
		});

		this.el.find('em.close-trigger').on('click', function(ev) {
			ev.preventDefault();
			self.close();
		});

		this.el.find('form').on('submit', function(ev) {
			ev.preventDefault();

			self.el.addClass('loading');
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: {
					message: $.trim(self.el.find('textarea').val() + "\n\n" + self.el.find('input[name="message_extra"]').val())
				},
				dataType: 'json',
				complete: function() {
					self.el.removeClass('loading')
				},
				success: function() {
					self.close();
					self.el.find('textarea').val('');
					DeskPRO_Window.showAlert("Thank you for submitting your feedback");
				}
			});
		});
	},

	open: function(message, messageExtra) {

		message = message || 'We want to hear about your experience with DeskPRO v4.';
		if (typeof message === 'string') {
			message = $('<span />').text(message);
		}

		$('#dp_submit_feedback_label').empty().append(message);
		this.el.find('input[name="message_extra"]').val(messageExtra || '');

		this.updatePositions();
		this.el.show();
	},

	close: function() {
		this.el.hide();
	},

	updatePositions: function() {
		var left = ($(window).width() / 2) - (this.el.outerWidth() / 2);
		this.el.css('left', left);

		this.el.show();
	}
});
