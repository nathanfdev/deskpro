Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Given a ticket details, runs through rules to fetch which fields should be displayed
 */
DeskPRO.Agent.PageHelper.TicketFieldDisplay = new Orb.Class({

	initialize: function(ticketReader) {
		this.ticketReader = ticketReader;
	},

	getFields: function(department_id) {
		department_id = parseInt(department_id);
		DP.console.log('[TicketFieldDisplay] department %i', department_id);

		if (typeof window.DESKPRO_TICKET_DISPLAY[department_id] == 'undefined') {
			var depItems = window.DESKPRO_TICKET_DISPLAY[0] || [];
		} else {
			var depItems = window.DESKPRO_TICKET_DISPLAY[department_id] || [];
		}

		DP.console.log('[TicketFieldDisplay] depItems %o', depItems);

		var items = this.runRules(depItems);
		DP.console.log('[TicketFieldDisplay] items %o', items);

		return items;
	},


	/**
	 * Run through all the rules and show/hide all display items and
	 * sections based on it.
	 */
	runRules: function(depItems) {

		var items = {};

		//------------------------------
		// Run all the rules to fetch on/off of each item in display
		//------------------------------

		Array.each(depItems, function(item) {
			if (!items[item.section]) {
				items[item.section] = [];
			}

			switch (item.section) {
				case 'default':
					var state = this.runCheckForItem(item);
					if (state) {
						items[item.section].push(item);
					}
					break;
			}

		}, this);

		return items;
	},

	/**
	 * Runs the check function for an item to get its visibility.
	 *
	 * @param item
	 */
	runCheckForItem: function(item) {
		var visible = true;

		// If the check function passes, then inverse visibility
		if (item.check && !item.check(this.ticketReader)) {
			visible = false;
		}

		return visible;
	}
});
