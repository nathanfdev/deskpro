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

		DeskPRO_Window.util.fileupload(this.el.find('.dp-form-row.new-picture'));
		this.el.find('.dp-form-row.new-picture').bind('fileuploadadd', function() {
			$('.files', form).empty();
		});

		var startEmail = $('#settings_profile_email').val();

		var changePass = false;
		var changeEmail = false;

		var verifyPasswords = function() {
			var pass1 = $('input.password1', form);
			var pass2 = $('input.password2', form);

			if (pass1.val().length) {
				changePass = true;
				if (pass1.val() != pass2.val()) {
					DeskPRO_Window.showAlert('Please enter the same password into both password fields', 'error');
					return false;
				}
			} else {
				changePass = false;
			}
			return true;
		};

		var checkEmailChange = function() {
			if ($('#settings_profile_email').val() != startEmail) {
				changeEmail = true;
			} else {
				changeEmail = false;
			}
		};

		var passCode = null;

		form.on('submit', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			if (!verifyPasswords()) {
				return;
			}
			checkEmailChange();

			if ((changePass || changeEmail) && !passCode) {
				$('#password_confirm').trigger('dp_open', {
					explain: "Confirm these changes to your profile by entering your current password.",
					success: function(code) {
						passCode = code;
						form.submit();
					}
				});
				return;
			}

			var data = $(this).serializeArray();
			if (passCode) {
				data.push({
					name: 'authcode',
					value: passCode
				});
			}
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: data,
				dataType: 'json',
				complete: function() {
					changePass = false;
					changeEmail = false;
					passCode = null;
					$('input.password1', form).val('');
					$('input.password2', form).val('');
				},
				success: function(data) {

					if (data.form_errors) {

						$('#agent_settings_win_errors').find('li').hide();
						Array.each(data.form_errors, function (code) {
							var classname = code.replace(/\./g, '_');
							$('#agent_settings_win_errors').find('li.' + classname).show();
						});
						$('#agent_settings_win_errors').show();

						return;
					}

					$('#agent_settings_win_errors').hide();

					self.settingsWindow.showSavePuff();
					startEmail = $('#settings_profile_email').val();
					self.settingsWindow.reloadInterface = true;
					self.settingsWindow.reloadTab('profile');
				}
			});
		});

		if (window.webkitNotifications) {
			var notificationsRow = el.find('.dp-desktop-notifications');
			notificationsRow.show();

			var permissionCallback = function() {
				var permission = window.webkitNotifications.checkPermission();

				if (permission == 0) {
					// granted
					notificationsRow.find('button').hide();
					notificationsRow.find('.dp-desktop-notifications-enabled').show();
				} else if (permission == 1) {
					// no action
					notificationsRow.find('button').show();
					notificationsRow.find('.dp-desktop-notifications-enabled').hide();
				} else {
					// explicitly denied
					notificationsRow.hide();
				}
			};

			permissionCallback();

			notificationsRow.find('button').click(function(e) {
				e.preventDefault();
				window.webkitNotifications.requestPermission(permissionCallback);
			});
		}
	}
});
