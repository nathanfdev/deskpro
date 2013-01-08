Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Urgency = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	setValue: function(value) {
		this.ticketPage.getEl('urgency').select2('val', value);
	},

	getValue: function() {
		return this.ticketPage.getEl('urgency').val();
	},

	getInterfaceElement: function() {
		return this.ticketPage.getEl('urgency');
	},

	getName: function() {
		return 'urgency';
	}
});
