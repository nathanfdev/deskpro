Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.PortalToggle = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var check = $(':checkbox', this.el);
		check.change(function() {
			if ($(this).is(':checked')) {
				$('#portal_nav').removeClass('disabled');
			} else {
				$('#portal_nav').addClass('disabled');
			}
		});
	}
});
