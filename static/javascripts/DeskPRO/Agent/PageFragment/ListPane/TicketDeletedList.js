Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketDeletedList = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,

	TYPENAME: 'ticket-deleted-list',

	resultTypeName: 'filter',
	resultTypeId: 0,

	initPage: function(el) {
		this.parent(el);
		this.resultTypeId = this.getMetaData('cache_id');
	}
});