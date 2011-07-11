Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.Notifications = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		$('.dismiss', this.el).click(function() {
			var li = $(this).parent();
			li.fadeOut();
		});

		$('.dismiss-all', this.el).click(function() {
			$('#user_notifs').slideUp();
		});
	}
});