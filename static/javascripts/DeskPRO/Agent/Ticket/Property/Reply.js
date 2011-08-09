Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

/**
 * New reply area
 */
DeskPRO.Agent.Ticket.Property.Reply = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	optionName: null,
	menuRepository: null,

	getName: function() {
		return 'reply';
	},

	getValue: function() {
		return this.ticketPage.getEl('replybox_txt').val();
	},

	highlightInterfaceElement: function() {
		return this.ticketPage.getEl('replybox').addClass('highlight-change-on');
	},

	unhighlightInterfaceElement: function() {
		return this.ticketPage.getEl('replybox').removeClass('highlight-change-on');
	},

	setValue: function(value) {
		this.getInterfaceElement().val(value);
	},

	setIncomingValue: function(value) {
		
	},

	_getInterfaceElement: function() {
		return this.ticketPage.getEl('replybox_txt');
	}
});