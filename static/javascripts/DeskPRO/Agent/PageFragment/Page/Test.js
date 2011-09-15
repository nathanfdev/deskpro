Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Test = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'test';
	},

	initPage: function(el) {

		var popover = new DeskPRO.Agent.PageHelper.Popover({
			pageUrl: BASE_URL + 'agent/people/20001'
		});
		popover.open();

		window.popover = popover;
	}
});
