Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.PersonPopout = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Page.Person,
	
	TYPENAME: 'person',
	
	initPage: function(el) {
		this.parent(el);
	}
});