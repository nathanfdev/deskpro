Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.InlineLoginForm = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		var self = this;

		this.options = {
			emailSel: '#dp_inline_login_email',
			passwordSel: '#dp_inline_login_pass',
			context: null
		};
		this.setOptions(options);

		this.context = this.options.context || document;

		this._initLoginForm($('.dp-inline-login', this.context));
	},

	_initLoginForm: function(wrapper) {
		var self = this;

		this.loginWrapper    = wrapper;
		this.passwordRow     = $('.dp-inline-login-pass', wrapper);
		this.nonloginWrapper = $('.dp-inline-non-login', wrapper);
		this.loginBtn        = $('.dp-login-trigger', wrapper);

		$('.dp-inline-login-open', wrapper).click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			if (this.loginWrapper.is('.open')) {
				this.closeLogin();
			} else {
				this.openLogin();
			}
		}).bind(this));

		this.loginBtn.click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			this.processLogin();
		}).bind(this));

		$(this.options.passwordSel, wrapper).keypress(function(ev) {
			if (ev.keyCode == 13) {
				ev.preventDefault();

				if (self.isOpen()) {
					self.processLogin();
				}
			}
		});
		$(this.options.emailSel, wrapper).keypress(function(ev) {
			if (ev.keyCode == 13) {
				ev.preventDefault();

				if (self.isOpen()) {
					self.processLogin();
				}
			}
		});
	},

	isOpen: function() {
		return this.loginWrapper.is('.open');
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
