Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Hold = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	optionName: 'is_hold',

	init: function() {

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

		if (value) {
			this.ticketPage.getEl('hold_message').show();
		} else {
			this.ticketPage.getEl('hold_message').hide();
		}
	},

	_formEl: null,
	getFormEl: function() {
		return $('input.is_hold:first', this.ticketPage.valueForm);
	}
});
