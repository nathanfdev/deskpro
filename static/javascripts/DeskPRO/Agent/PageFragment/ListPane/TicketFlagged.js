Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketFlagged = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,

	TYPENAME: 'ticket-flagged',

	resultTypeName: 'flagged',
	resultTypeId: 0,

	initPage: function(el) {
		DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults.prototype.initPage.apply(this, [el]);
		this.resultTypeId = this.getMetaData('flag');
	}
});