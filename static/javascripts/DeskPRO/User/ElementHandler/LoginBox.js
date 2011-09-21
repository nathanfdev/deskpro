Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.LoginBox = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.loginLink = $('#dp_login_link');
		this.loginBox = $('#dp_login_box');
		this.loginBoxTitle = $('#dp_login_box_title');

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
		}).bind(this));
	}
});
