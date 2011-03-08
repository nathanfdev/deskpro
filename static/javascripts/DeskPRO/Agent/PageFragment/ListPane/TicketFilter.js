Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketFilter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,

	resultTypeName: 'filter',
	resultTypeId: 0,

	initPage: function(el) {
		this.parent(el);
		this.resultTypeId = this.getMetaData('filter_id');
	},

	activate: function() {
		if (this.getMetaData('filter_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('filter.view-activated', this.getMetaData('filter_id'));
		}
	},

	deactivate: function() {
		if (this.getMetaData('filter_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('filter.view-deactivated', this.getMetaData('filter_id'));
		}
	}
});