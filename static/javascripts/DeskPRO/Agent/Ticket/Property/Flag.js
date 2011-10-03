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
		return $('li.on', this.ticketPage.getEl('flag_opt')).data('value');
	},

	setValue: function(value) {
		console.log('set %o', value);
		$('li', this.ticketPage.getEl('flag_opt')).removeClass('on');
		$('li.flag-' + value, this.ticketPage.getEl('flag_opt')).addClass('on');
	}
});
