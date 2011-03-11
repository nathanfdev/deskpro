Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Status = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.StandardOption,

	setValue: function(value) {
		this.parent(value);
		console.log(value);

		this.getInterfaceElement().removeClass('ticket-open ticket-closed ticket-pending ticket-resolved ticket-hidden').addClass('ticket-' + value);
	}
});