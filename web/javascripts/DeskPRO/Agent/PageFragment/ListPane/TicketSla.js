Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketSla = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'ticket-sla';

		this.resultTypeName = 'sla';
		this.resultTypeId = 0;
	},

	initPage: function(el) {

		DeskPRO_Window.getMessageBroker().sendMessage('ticket-section.list-activated', { listType: 'sla', id: this.getMetaData('sla_id'), topGroupingOption: this.meta.topGroupingOption || null });
		this.resultTypeId = this.getMetaData('sla_id');
		this.parent(el);
	},

	activate: function() {
		if (this.getMetaData('sla_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('sla.view-activated', this.getMetaData('sla_id'));
		}
	},

	deactivate: function() {
		if (this.getMetaData('sla_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('sla.view-deactivated', this.getMetaData('sla_id'));
		}
	},

	updateSlaDisplay: function(info) {
		if (!info.ticket_id || !info.sla_id) {
			return;
		}

		if (info.sla_id == this.getMetaData('sla_id')) {
			this.refreshSlaTicketList();
		}
	},

	refreshSlaTicketList: function() {
		DeskPRO_Window.loadListPane(this.meta.refreshUrl);
	}
});
