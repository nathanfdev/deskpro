Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Ticket');

/**
 * Handles functionality around editing of ticket fields. Standard fields
 * like category or product, but also custom fields in tabs and the field editor.
 */
DeskPRO.Agent.PageFragment.Page.Ticket.TicketFields = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		this.options = {

		};

		this.page = page;

		$('.properties-edit-trigger', this.page.wrapper).click(this.showEditor.bind(this));
	},

	getEl: function(id) {
		return this.page.getEl(id);
	}
});