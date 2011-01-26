Orb.createNamespace('DeskPRO.Agent.TicketList.Property');

/**
 * New reply area
 */
DeskPRO.Agent.TicketList.Property.NewReply = new Class({
	Extends: DeskPRO.Agent.TicketList.Property.Abstract,
	
	getName: function() {
		return 'new_reply';
	},
	
	getValue: function() {
		return '';
	},
	
	setValue: function(value) {
		
	},
	
	setIncomingValue: function(value) {

	},
	
	_getInterfaceElement: function() {
		//return $('form.reply-form textarea[name="message"]:first', this.ticketPage.ticketReply);
	}
});