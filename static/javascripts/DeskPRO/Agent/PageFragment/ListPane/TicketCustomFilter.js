Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketCustomFilter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,

	TYPENAME: 'ticket-custom-filter',

	resultTypeName: 'filter',
	resultTypeId: 0,

	initPage: function(el) {
		this.parent(el);
		this.resultTypeId = this.getMetaData('cache_id');
	}
});