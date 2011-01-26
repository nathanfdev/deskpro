Orb.createNamespace('DeskPRO.Agent.TicketList');

/**
 * Handles changes to many tickets on a ticket page. Somewhat same idea as with the Ticket.ChangeManager,
 * except we work with many tickets at a time, and we only care about updating the UI and not about 
 * getting current values.
 */
DeskPRO.Agent.TicketList.ChangeManager = new Class({
	
	ticketPage: null,

	oldValues: {},
	changes: {},
	
	ticketIds: null,

	/**
	 * @param {DeskPRO.Agent.PageFragment.Page.BasicTicketResults} ticketPage
	 */
	initialize: function(ticketPage) {
		this.ticketPage = ticketPage;
	},
	
	
	
	/**
	 * Begin changes to selected tickets
	 *
	 * @param {Array} ticketIds
	 */
	begin: function(ticketIds) {
		if (this.ticketIds !== null) {
			throw "activeTicketIds already set, you cannot begin a new one until the last set were committed or reverted.";
		}
		
		this.ticketIds = ticketIds;
		this.oldValues = {};
		this.changes = {};
	},
	
	
	
	/**
	 * Add a change to the set of changes. This applies a change to all tickets.
	 */
	addChange: function(property, newValue, applyNow) {

		var name = property.getName();
		var id = property.getTicketId();
		
		// Dont care if its the same!
		if (property.isSameValue(newValue)) {
			//return;
		}
		
		if (!this.changes[id]) this.changes[id] = {};
		this.changes[id][name] = [property, newValue];
		
		if (applyNow) {
			this.applyChangeForProperty(property, newValue);
		}
	},
	
	
	
	/**
	 * Apply a certain new value in the interface
	 */
	applyChangeForProperty: function (property, newValue) {
		
		var name = property.getName();
		var id = property.getTicketId();
		
		if (!this.oldValues[id]) this.oldValues[id] = {};
		
		this.oldValues[id][name] = property.getValue();
		property.setValue(newValue);
		
		property.highlightInterfaceElement();
	},
	
	
	
	/**
	 * Apply all queued changes in the interface
	 */
	applyChanges: function() {
		
		$('tr:not(.on, .line-3)', this.ticketPage.contentWrapper).addClass('faded');
		$('tr.on', this.ticketPage.contentWrapper).removeClass('on');

		Array.each(Object.values(this.changes), function (changes) {
			Object.each(changes, function(change) {

				var property = change[0];
				var newValue = change[1];
			
				this.applyChangeForProperty(property, newValue);
			}, this);
		}, this);
	},
	
	
	
	/**
	 * Revert all queuued changes in the interface to their previuos values
	 */
	revertChanges: function() {

		Array.each(Object.values(this.changes), function (changes) {
			Object.each(changes, function(change) {
				var property = change[0];
				var name = property.getName();
				var id = property.getTicketId();
			
				if (this.oldValues[id][name] !== undefined) {
					property.setValue(this.oldValues[id][name]);
					property.unhighlightInterfaceElement();
				}
			}, this);
		}, this);
		
		$('tr', this.ticketPage.contentWrapper).removeClass('with-line-3').removeClass('faded');
		$('tr.line-3', this.ticketPage.contentWrapper).html('<ul></ul>').hide();
		
		this.ticketPage.actionBarHelper._selectOp('none');
		
		this.ticketIds = null;
		this.oldValues = {};
		this.changes = {};
	},
	
	
	
	/**
	 * Just updates the UI to show we accepted the changes
	 */
	commitChanges: function() {
		Array.each(Object.values(this.changes), function (changes) {
			Object.each(changes, function(change) {
				var property = change[0];
				property.unhighlightInterfaceElement();
			}, this);
		}, this);
		
		$('tr', this.ticketPage.contentWrapper).removeClass('with-line-3').removeClass('faded');
		$('tr.line-3', this.ticketPage.contentWrapper).html('').hide();
		
		this.ticketPage.actionBarHelper._selectOp('none');
		
		this.ticketIds = null;
		this.oldValues = {};
		this.changes = {};
	},



	/**
	 * Called when we detect if a value was updated automatically from somewhere.
	 */
	setPropertyUpdated: function(property, newValue) {
		if (typeOf(property) == 'string') {
			property = this.ticketPage.getPropertyManager(property);
		}
		
		property.setIncomingValue(newValue);
		property.pulseInterfaceElement();
	}
});