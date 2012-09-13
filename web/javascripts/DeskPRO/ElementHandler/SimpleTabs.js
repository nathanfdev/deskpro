Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.ElementHandler.SimpleTabs = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var triggerElements = $(this.el.data('trigger-elements') || 'ul:first li', this.el);

		this.simpleTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: triggerElements
		});

		this.el.data('simpletabs', this.simpleTabs);
	}
});
