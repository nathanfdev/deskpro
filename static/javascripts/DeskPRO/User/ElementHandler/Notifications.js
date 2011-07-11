Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.Notifications = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		var self = this;
		$('.dismiss', this.el).click(function(ev) {
			var li = $(this).parent();

			if ($('li', self.el).length > 1) {
				li.fadeOut('fast', function() { li.remove(); });
			} else {
				$('#user_notifs').fadeOut();
			}
		});

		$('.dismiss-all', this.el).click(function(ev) {
			ev.preventDefault();
			$('#user_notifs').slideUp();
		});
	}
});