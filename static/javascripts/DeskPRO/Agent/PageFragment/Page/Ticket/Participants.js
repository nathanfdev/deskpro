Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Ticket');

/**
 * Management of participants in the ticket
 */
DeskPRO.Agent.PageFragment.Page.Ticket.Participants = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {

		var self = this;

		this.page = page;
		this.options = {
		};

		this.setOptions(options);

		
	}
});