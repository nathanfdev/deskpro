Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.InlineLoginForm = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			emailSel: '#dp_inline_login_email',
			passwordSel: '#dp_inline_login_pass',
			context: this
		};
		this.setOptions(options);

		this.context = this.options.context || document;

		this.loginWrapper    = $('.dp-inline-login', this.context);
		this.passwordRow     = $('.dp-inline-login-pass', this.context);
		this.nonloginWrapper = $('.dp-inline-non-login', this.context);
		this.loginBtn        = $('.dp-login-trigger', this.context);

		$('.dp-inline-login-open', this.context).click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			if (!this.loginWrapper.is('.open')) {
				this.openLogin();
			} else {
				this.closeLogin();
			}
		}).bind(this));

		this.loginBtn.click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			this.processLogin();
		}).bind(this));
	},

	openLogin: function() {
		this.loginWrapper.addClass('open');
		this.passwordRow.slideDown('fast');
		this.nonloginWrapper.animate({ opacity: '0.4', duration: 'fast' });
	},

	closeLogin: function() {
		this.passwordRow.slideUp('fast', (function() {
			this.loginWrapper.removeClass('open');
		}).bind(this));
		this.nonloginWrapper.animate({ opacity: '1', duration: 'fast' });
	},

	processLogin: function() {
		var postData = [];
		postData.push({
			name: 'email',
			value: $(this.options.emailSel, this.context).val()
		});
		postData.push({
			name: 'password',
			value: $(this.options.passwordSel, this.context).val()
		});

		$.ajax({
			url: BASE_URL + 'login/inline-login',
			type: 'POST',
			data: postData,
			dataType: 'json',
			context: this,
			success: function(data) {
				var newEl = $(data.html);
				if (data.person_id) {
					$('#dp_inline_login_row').replaceWith(newEl);
					this.nonloginWrapper.css({ opacity: '1'});
				} else {
					$('#dp_inline_login_row').replaceWith(newEl);
					$('.dp-inline-login-pass', newEl).show();
				}

				if (data.sections_replace) {
					Object.each(data.sections_replace, function(html, id) {
						$('#' + id).empty().replaceWith(html);
					});
				}

				this._initLoginForm(newEl);

				this.fireEvent('success', [data, this]);
			}
		})
	}
});
