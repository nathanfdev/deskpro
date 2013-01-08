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

		$('.set-hold', this.ticketPage.wrapper).hide();

		if (value) {
			$('.set-hold.unhold', this.ticketPage.wrapper).show();
		} else {
			$('.set-hold.hold', this.ticketPage.wrapper).show();
		}

		this.getFormEl().val(value);
	},

	_formEl: null,
	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = $('input.is_hold:first', this.ticketPage.valueForm);

		return this._formEl;
	}
});
