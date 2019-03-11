Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Hold = new Orb.Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	init: function() {
		this.optionName = 'is_hold';
	},

	getName: function() {
		return this.optionName;
	},

	getValue: function() {
		return this.getFormEl().val();
	},

	setValue: function(value) {
		if (typeof value === 'boolean') {
			value = value ? 1 : 0;
		} else {
			value = parseInt(value);
		}

		this.getFormEl().val(value);
	},

	getFormEl: function() {
		return $('input.is_hold:first', this.ticketPage.valueForm);
	}
});
