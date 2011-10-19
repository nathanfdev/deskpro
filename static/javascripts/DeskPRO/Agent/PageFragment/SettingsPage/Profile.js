Orb.createNamespace('DeskPRO.Agent.PageFragment.SettingsPage');

DeskPRO.Agent.PageFragment.SettingsPage.Profile = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'settings_profile';
	},

	initPage: function(el) {
		var self = this;
		this.el = el;

		var form = $('form', this.el);

		var verifyPasswords = function() {
			var pass1 = $('input.password1', form);
			var pass2 = $('input.password2', form);

			if (pass1.val().length) {
				if (pass1.val() != pass2.val()) {
					DeskPRO_Window.showAlert('Please enter the same password into both password fields', 'error');
					return false;
				}
			}
		};

		form.submit(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			if (!verifyPasswords()) {
				return;
			}

			var data = $(this).serializeArray();
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function() {
					self.settingsWindow.showSavePuff();
				}
			});
		});
	}
});
