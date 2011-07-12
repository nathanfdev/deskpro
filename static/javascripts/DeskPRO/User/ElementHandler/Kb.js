Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.Kb = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {

		var triggers = $('.heading-tabs li', this.el);
		this.headerTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('.heading-tabs li', this.el),
			context: $('.tab-contents', this.el)
		});
	}
});