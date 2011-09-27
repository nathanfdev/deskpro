Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.CommentFormLogin = new Orb.Class({
	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		var self = this;
		var a = $('a', this.el).first();
		a.click(function(ev) {
			ev.preventDefault();
			self.openWindow($(this).attr('href'));
		});

		this.type = this.el.data('auth-type');

		this.jsTell = null;
	},

	getJsTell: function() {
		if (this.jsTell) return this.jsTell;
		var self = this;
		var uid = Orb.uuid();

		this.jsTell = uid;
		window[uid] = function(data) {
			self.authFinished(data);
		};

		return this.jsTell
	},

	authFinished: function(data) {
		$('#comments_login_info .email-address-row').hide();

		switch (this.type) {
			case 'twitter':
				var link = '<a href="http://twitter.com/' + data.identity_friendly + '">' + data.identity_friendly + '</a>';
				$('#comments_login_info .source-extra-name').html(link);
				$('#comments_login_info .source-extra').show();
				$('#comments_login_info .display-name-field').val(data.fullname);
				break;

			case 'facebook':
				var link = '<a href="' + data.link + '">' + data.name + '</a> (' + data.email + ')';
				$('#comments_login_info .source-extra-name').html(link);
				$('#comments_login_info .source-extra').show();
				$('#comments_login_info .display-name-field').val(data.name);
				break;

			default:
				var link = data.person_name;
				if (data.person_email) {
					link += ' (' + data.person_name + ')';
				}
				$('#comments_login_info .source-extra-name').html(link);
				$('#comments_login_info .source-extra').show();
				$('#comments_login_info .display-name-field').val(data.person_name);
				break;
		}

		$('#comments_login_info .nav').hide();
		$('#comments_login_info').addClass('no-nav');
	},

	openWindow: function(url){
		url = Orb.appendQueryData(url, 'js_tell', this.getJsTell());
		window.open(url,this.getJsTell(),'width=600,height=350,location=0,menubar=0,scrollbars=0,status=0,toolbar=0,resizable=0');
	}
});
