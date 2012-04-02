Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Urgency = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	setValue: function(value) {
		this.ticketPage.wrapper.find('.marker-urgency-data').data('urgency', value).attr('data-urgency', value);
		this.ticketPage.getEl('urgency').data('urgency', value).attr('data-urgency', value).text(value);
	},

	getValue: function() {
		return this.ticketPage.getEl('urgency').data('urgency');
	},

	getInterfaceElement: function() {
		return this.ticketPage.getEl('urgency');
	},

	getName: function() {
		return 'urgency';
	}
});
