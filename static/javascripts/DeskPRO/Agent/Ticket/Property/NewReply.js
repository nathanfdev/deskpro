Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

/**
 * New reply area
 */
DeskPRO.Agent.Ticket.Property.NewReply = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,
	
	optionName: null,
	menuRepository: null,
	
	getName: function() {
		return 'new_reply';
	},
	
	getValue: function() {
		return $('form.reply-form', this.ticketPage.ticketReply).serializeArray();
	},
	
	setValue: function(value) {
		this.ticketPage.toggleReplyBar('on');
		this.getInterfaceElement().val(value);
	},
	
	setIncomingValue: function(value) {
		this.ticketPage.toggleReplyBar('off');
		this.ticketPage.displayNewMessage(value);
		$('textarea[name="message"]', this.ticketPage.ticketReply).val('');
	},
	
	_getInterfaceElement: function() {
		return $('form.reply-form textarea[name="message"]:first', this.ticketPage.ticketReply);
	}
});