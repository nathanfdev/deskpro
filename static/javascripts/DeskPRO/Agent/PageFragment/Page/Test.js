Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Test = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'test',

	initPage: function(el) {

		var popover = new DeskPRO.Agent.PageHelper.Popover({
			pageUrl: BASE_URL + 'agent/people/20001'
		});
		popover.open();

		window.popover = popover;
	}
});