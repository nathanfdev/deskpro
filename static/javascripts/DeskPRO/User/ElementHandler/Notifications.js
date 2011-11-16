Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.Notifications = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {

		this.placeholder = $('#user_notifs_placeholder');
		this.notif = $('#user_notifs');

		var self = this;
		$('.dismiss', this.el).on('click', function(ev) {
			var li = $(this).parent();

			if ($('li', self.el).length > 1) {
				li.fadeOut('fast', function() { li.remove(); });
			} else {
				$('#user_notifs').fadeOut();
			}
		});

		$('.dismiss-all', this.el).on('click', function(ev) {
			ev.preventDefault();
			$('#user_notifs').slideUp();
		});

		$('.view-all', this.el).on('click', function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			self.popOpen();
		});

		$('.close-trigger', this.el).on('click', (function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			self.popClose();
		}).bind(this));
	},

	popOpen: function() {
		var pos = this.placeholder.offset();

		this.notif.slideUp('fast', (function() {
			this.notif.css({
				top: pos.top + 4,
				left: pos.left - 23,
				width: this.placeholder.width() + 15,
				position: 'absolute'
			});
			$('ul:first', this.notif).css({
				'max-height': 400,
				overflow: 'auto'
			});
			$('ul:first > li', this.notif).show();

			this.notif.addClass('open');

			this.notif.detach().appendTo('body').slideDown();
		}).bind(this));
	},

	popClose: function() {
		this.notif.slideUp('fast', (function() {
			this.notif.css({
				top: '',
				left: '',
				width: '',
				position: 'relative'
			});

			$('ul:first', this.notif).css({
				'max-height': '',
				overflow: 'visible'
			});
			$('ul:first > li:gt(5)', this.notif).hide();

			this.notif.removeClass('open');

			this.notif.detach().insertAfter(this.placeholder).slideDown();
		}).bind(this));
	}
});
