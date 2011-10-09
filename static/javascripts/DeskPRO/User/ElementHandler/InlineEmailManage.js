Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.InlineEmailManage = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		var self = this;
		this.emailField = $('.dp-email-field', this.el).click(function() {
			$(this).blur();
		});
		this.newEmailField = $('.dp_inline_email_new', this.el);
		this.emailList = $('.dp-email-manage-list', this.el);
		this.controlsEl = $('.dp-email-manage-controls', this.el);
		$('input[name="dp_inline_email_choice"]', this.controlsEl).click(function() {
			var val = $(this).val();
			if (val == 'NEW') {
				self.setNewMode();
			} else {
				self.emailField.val(val);
			}
		});

		this.emailError = $('.error-message', this.el);

		this.newEmailField.keypress(function(ev) {
			if (ev.keyCode == 13) {
				ev.preventDefault();
				ev.stopPropagation();
			}
		});
		this.newEmailField.keyup(function(ev) {
			if (self.mode == 'new') {
				self.emailField.val(self.newEmailField.val());
			}
		});

		this.mode = 'normal';

		if ($('input[name="dp_inline_email_choice"]:selected').val() == 'NEW') {
			this.setNewMode();
		}

		var changeEmail = $('.change-email', this.el).click(function(ev) {
			ev.preventDefault();
			self.controlsEl.slideDown('fast');
			changeEmail.hide();
			changeEmailClose.show();
		});
		var changeEmailClose =  $('.change-email-close', this.el).click(function(ev) {
			ev.preventDefault();
			self.controlsEl.slideUp('fast');
			changeEmail.show();
			changeEmailClose.hide();
		});
	},

	setNormalMode: function() {
		this.mode = 'normal';
	},

	setNewMode: function() {
		this.mode = 'new';
		this.emailField.val(this.newEmailField.val());
	}
});
