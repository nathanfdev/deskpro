Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTicket = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Basic,
	
	TYPENAME: 'ticket',

	wrapper: null,
	popout: null,
	popout_overview: null,
	
	isMouseOverPopout: false,
	hasInitPopout: false,
	popoutPage: null,
	
	destroyEls: [],
	destroyMenus: [],
	
	initPage: function(el) {

		this.wrapper = el;

	}
});