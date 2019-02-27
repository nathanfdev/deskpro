Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Given a ticket details, runs through rules to fetch which fields should be displayed
 */
DeskPRO.Agent.PageHelper.TicketFieldDisplay = new Orb.Class({

	initialize: function(ticketReader, mode) {
		this.ticketReader = ticketReader;
		this.mode = mode || 'create';
	},

	getFields: function(department_id) {
		department_id = parseInt(department_id);
		console.log('[TicketFieldDisplay] department %i', department_id);

		var depItems = [], layout, filterFn;
		if (window.DESKPRO_TICKET_DISPLAY) {
			layout = window.DESKPRO_TICKET_DISPLAY.getLayout(department_id);
			depItems = layout.getFields();

			switch (this.mode) {
				case 'view':
					filterFn = function(i) { return (!!i.isVisibleOnView) || (!!i.isVisibleOnViewAlways); };
					break;
				case 'modify':
					filterFn = function(i) { return !!i.isVisibleOnEdit; };
					break;
				case 'create':
					filterFn = function(i) { return !!i.isVisibleOnNew; };
					break;
			}

			if (filterFn) {
				depItems = depItems.filter(filterFn);
			}
		}

		console.log('[TicketFieldDisplay] depItems %o', depItems);

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
			var section = 'default';
			if (!items[section]) {
				items[section] = [];
			}

			switch (section) {
				case 'default':
					var state = this.runCheckForItem(item);
					if (state) {
						items[section].push(item);
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
		if (item.checkFn && !item.checkFn(this.ticketReader)) {
			visible = false;
		}

		return visible;
	},

	destroy: function() {
		this.ticketReader = null;
	}
});
