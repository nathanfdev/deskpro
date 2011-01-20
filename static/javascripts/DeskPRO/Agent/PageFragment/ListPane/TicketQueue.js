Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketQueue = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,
	
	resultTypeName: 'queue',
	resultTypeId: 0,

	initPage: function(el) {
		this.parent(el);
		this.resultTypeId = this.getMetaData('queue_id');
	},
	
	activate: function() {
		if (this.getMetaData('queue_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('queue.view-activated', this.getMetaData('queue_id'));
		}
	},

	deactivate: function() {
		if (this.getMetaData('queue_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('queue.view-deactivated', this.getMetaData('queue_id'));
		}
	}
});