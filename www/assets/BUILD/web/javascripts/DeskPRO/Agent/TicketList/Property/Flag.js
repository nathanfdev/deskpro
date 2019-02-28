Orb.createNamespace('DeskPRO.Agent.TicketList.Property');

DeskPRO.Agent.TicketList.Property.Flag = new Orb.Class({
	Extends: DeskPRO.Agent.TicketList.Property.Abstract,

	init: function() {
		this.optionName = 'flag';
		this.displayCaption = 'Flag';
	},

	getValue: function() {
		return this.getInterfaceElement().data('flag');
	},

	getName: function() {
		return this.optionName;
	},

	setValue: function(value) {
		var last_value = this.getInterfaceElement().data('flag');

		this.getInterfaceElement().data('flag', value);
		this.getInterfaceElement().removeClass('icon-flag-' + last_value).addClass('icon-flag-' + value);
	},

	_getInterfaceElement: function() {
		var el = $(this._buildSelector('.ticket-flag:first'), this.ticketPage.actionsBarHelper.tableEl);
		return el;
	}
});