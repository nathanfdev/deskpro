Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

/**
 * Special loading fragment that just shows "Loading..." until a page has actually loaded
 */
DeskPRO.Agent.PageFragment.Page.Loading = new Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,

	allowDupe: true,
	TYPENAME: 'loading',

	initPage: function(el) {
		this.wrapper = $(el);
	}
});
