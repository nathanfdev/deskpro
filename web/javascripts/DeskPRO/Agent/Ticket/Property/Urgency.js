Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Urgency = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	setValue: function(value) {

		var classlist = 'urgency-1 urgency-2 urgency-3 urgency-4 urgency-5 urgency-6 urgency-7 urgency-8 urgency-9 urgency-10';
		var addclass = 'urgency-' + value;

		this.ticketPage.wrapper.find('.marker-urgency-data').removeClass(classlist).addClass(addclass);
		this.ticketPage.getEl('urgency').data('urgency', value).text(value);
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
