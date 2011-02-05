Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Flag = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	optionName: 'flag',

	init: function() {

	},

	getName: function() {
		return this.optionName;
	},

	getValue: function() {
		return this.getInterfaceElement().data('flag');
	},

	setValue: function(value) {
		var last_value = this.getInterfaceElement().data('flag');

		this.getInterfaceElement().data('flag', value);
		this.getInterfaceElement().removeClass('icon-flag-' + last_value).addClass('icon-flag-' + value);
	},

	_getInterfaceElement: function() {
		return $('.ticket-flag:first', this.ticketPage.contentWrapper);
	}
});