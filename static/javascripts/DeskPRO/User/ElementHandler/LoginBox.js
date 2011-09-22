Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.LoginBox = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.loginLink = $('#dp_login_link');
		this.loginBox = $('#dp_login_box');
		this.loginBoxTitle = $('#dp_login_box_title');

		this.loginSection = $('.dp-login-section', this.el);
		this.resetSection = $('.dp-reset-section', this.el);

		this.loginLink.click((function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			this.open();
		}).bind(this));

		this.loginBoxTitle.click((function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			this.close();
		}).bind(this));

		this.loginBox.click(function(ev) {
			// dont bubble click to document which'll close this
			ev.stopPropagation();
		});

		this._initResetSection();

		$(document).click(this.close.bind(this));
	},

	updatePositions: function() {
		var linkPos  = this.loginLink.offset();
		var linkW = this.loginLink.width();
		var linkH = this.loginLink.height();

		var titleW = this.loginBoxTitle.outerWidth();
		var titleH = this.loginBoxTitle.outerHeight();

		var offT = -2;
		var offL = -8;

		this.loginBoxTitle.css({
			top:  linkPos.top + offT,
			left: linkPos.left + offL
		});

		var headerPos = $('#dp_header_bar').offset();
		var headerW = $('#dp_header_bar').width();

		var loginBoxW = this.loginBox.width();

		this.loginBox.css({
			top: linkPos.top + offT + titleH,
			left: headerPos.left + headerW - loginBoxW
		});
	},

	open: function() {
		this.updatePositions();
		this.loginBoxTitle.show();
		this.loginBox.slideDown('fast');
	},

	close: function() {
		this.loginBox.slideUp('fast', (function() {
			this.loginBoxTitle.hide();
			this.hideReset(true);
		}).bind(this));
	},

	//#########################################################################
	//# Reset Stuff
	//#########################################################################

	_initResetSection: function() {
		$('.forgot', this.el).click((function(ev) {
			ev.preventDefault();
			this.showReset();
		}).bind(this));

		$('.back', this.resetSection).click((function(ev) {
			this.hideReset();
		}).bind(this));

		$('.dp-do-send', this.resetSection).click((function(ev) {
			ev.preventDefault();
			this.sendReset();
		}).bind(this));
	},

	sendReset: function() {

		this.resetSection.addClass('loading');

		$.ajax({
			url: BASE_URL + 'login/reset-password/send',
			type: 'POST',
			data: {
				email: $('#dp_login_email').val()
			},
			dataType: 'json',
			context: this,
			success: function() {
				this.resetSection.removeClass('loading');

				var descEl = $('.dp-reset-desc', this.resetSection);
				var sentEl = $('.dp-reset-sent', this.resetSection);

				descEl.slideUp('fast', function() {
					sentEl.slideDown();
				});
			}
		});
	},

	showReset: function() {
		this.loginSection.slideUp('fast', (function() {
			this.resetSection.slideDown('fast');
		}).bind(this));
	},

	hideReset: function(quick) {
		if (quick) {
			this.resetSection.hide();
			this.loginSection.show();
			$('.dp-reset-desc', this.resetSection).show();
			$('.dp-reset-sent', this.resetSection).hide();
		} else {
			this.resetSection.slideUp('fast', (function() {
				this.loginSection.slideDown('fast');

				// Also reset view on others
				$('.dp-reset-desc', this.resetSection).show();
				$('.dp-reset-sent', this.resetSection).hide();
			}).bind(this));
		}
	}
});
