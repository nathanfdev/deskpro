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
		console.log('[TicketFieldDisplay] department %i', department_id);

		if (!window.DESKPRO_TICKET_DISPLAY || !window.DESKPRO_TICKET_DISPLAY[department_id]) {
			// The department is empty of fields
			// (Rare, because we'll at least have category and such usually)
			return [];
		}

		var depItems = window.DESKPRO_TICKET_DISPLAY[department_id];
		console.log('[TicketFieldDisplay] depItems %o', depItems);

		//depItems = this.runRules(depItems);

		var items = this.runRules(depItems);
		console.log('[TicketFieldDisplay] items %o', items);

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
	 * Returns array of:
	 * 0: The item ID
	 * 1: The items visibility
	 *
	 * @param item
	 */
	runCheckForItem: function(item) {
		return true;

		var itemId = this.getItemId(item);
		if (item.initial_display == 'visible') {
			var visible = true;
		} else {
			var visible = false;
		}

		// If the check function passes, then inverse visibility
		if (item.check && item.check(this.ticketReader)) {
			visible = !visible;
		}

		return [itemId, visible];
	}
});
