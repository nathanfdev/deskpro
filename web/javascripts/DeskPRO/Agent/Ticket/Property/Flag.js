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
		return this.ticketPage.getEl('flag').val();
	},

	getInterfaceElement: function() {
		return this.ticketPage.getEl('flag');
	},

	setValue: function(value) {
		DP.console.log('set %o', value);

		var old_flag = $('li.last-on', this.ticketPage.getEl('flag_opt')).removeClass('last-on').data('value');

		$('li', this.ticketPage.getEl('flag_opt')).removeClass('on');
		var new_flag = $('li.flag-' + value, this.ticketPage.getEl('flag_opt')).addClass('on last-on').data('value');

		if (!value || value == "") {
			DeskPRO_Window.util.modCountEl(this.ticketPage.getEl('flag_count'), '=', 0);
		} else {
			DeskPRO_Window.util.modCountEl(this.ticketPage.getEl('flag_count'), '=', 1);
		}

		if (DeskPRO_Window.sections.tickets_section) {
			DeskPRO_Window.sections.tickets_section.changeFlagCountsForSwitch({
				old_flag: old_flag,
				new_flag: new_flag
			});
		}
	}
});
