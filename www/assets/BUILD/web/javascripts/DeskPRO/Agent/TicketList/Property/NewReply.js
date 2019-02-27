Orb.createNamespace('DeskPRO.Agent.TicketList.Property');

/**
 * New reply area
 */
DeskPRO.Agent.TicketList.Property.NewReply = new Orb.Class({
	Extends: DeskPRO.Agent.TicketList.Property.Abstract,

	init: function() {
		this.displayCaption = 'Reply';
	},

	getName: function() {
		return 'new_reply';
	},

	isSameValue: function(compare) {
		return false;
	},

	getValue: function() {
		return null;
	},

	setValue: function(value) {
		if (value) {
			var text = value;
			this.getInterfaceElement().removeClass('no-value').text(text);
		}
	},

	setIncomingValue: function(value) {

	},

	_getInterfaceElement: function() {
		return this.getSublineElement();
	}
});