Orb.createNamespace('DeskPRO.Agent.Ticket');

/**
 * Handles changes to a ticket.
 */
DeskPRO.Agent.Ticket.ChangeManager = new Class({
	
	ticketPage: null,
	ticketId: null,
	updateUrl: null,

	mode: 'single',
	oldValues: {},
	changes: {},

	/**
	 * @param {DeskPRO.Agent.PageFragment.Page.Ticket} ticketPage
	 */
	initiate: function(ticketPage) {
		this.ticketPage = ticketPage;
		this.ticketId   = ticketPage.getMetaData('ticket_id');
		this.updateUrl  = ticketPage.getMetaData('updateUrl');
	},
	
	
	/**
	 * Add a change to the set of changes
	 */
	addChange: function(property, newValue, applyNow) {
		this.changes[property.getName()] = [property, newValue];
		
		if (applyNow) {
			this.applyChangeForProperty(property, newValue);
		}
	},
	
	
	
	/**
	 * Apply a certain new value in the interface
	 */
	applyChangeForProperty: function (property, newValue) {
		var property = change[0];
		var newValue = change[1];

		this.oldValues[property.getName()] = property.getValue();
		property.setValue(newValue);
		
		if (this.mode == 'multi') {
			property.highlightInterfaceElement();
		}
	},
	
	
	
	/**
	 * Apply all queued changes in the interface
	 */
	applyChanges: function() {	
		Object.each(this.changes, function (change) {

			var property = change[0];
			var newValue = change[1];
			
			this.applyChangeForProperty(property, newValue);
		}, this);
	},
	
	
	
	/**
	 * Revert all queuued changes in the interface to their previuos values
	 */
	revertChanges: function() {
		Object.each(this.changes, function (change) {
			var property = change[0];
			var name = property.getName();
			
			if (this.oldValues[name] !== undefined) {
				property.setValue(this.oldValues[name]);
				property.unhighlightInterfaceElement();
			}
		}, this);
		
		this.oldValues = {};
	},
	
	
	
	/**
	 * Set a property change now, no queueing. This will fall back into
	 * queue mode if we're already in a queued state.
	 */
	setInstantChange: function(property, newValue) {
		
		// We're already in multi-mode, add this to queue the changes
		if (this.mode == 'multi') {
			this.addChange(property, newValue, true);
			return;
		}
		
		// Otherwise change and send one
		property.setValue(newValue);
		
		var data = [];
		this._addPropertyValueToData(data, property.getValue());
		
		return;
		$.ajax({
			type: 'POST',
			url: this.updateUrl,
			data: data,
			dataType: 'json',
			success: function(data) {
				console.log(data);
			}
		});
	},


	/**
	 * Save the changes for all queued items
	 */
	saveChanges: function() {
		var data = [];
		
		for (var i = 0; i < this.changes.length; i++) {
			var property = this.changes[i][0];
			var name = property.getName();
			
			this._addPropertyValueToData(data, property.getValue());
			property.unhighlightInterfaceElement();
		}
		
		return;
		$.ajax({
			type: 'POST',
			url: this.updateUrl,
			data: data,
			dataType: 'json',
			success: function(data) {
				console.log(data);
			}
		});
	},
	
	_addPropertyValueToData: function(data, propertyValue) {
		if (typeOf(propertyValue) != 'array') {
			value = [propertyValue];
		}
		
		for (var x = 0; x < propertyValue.length; x++) {
			data.push({
				name: name,
				value: value[x]
			});
		}
	},
	
	
	
	/**
	 * Called when we detect if a value was updated automatically from somewhere.
	 */
	setPropertyUpdated: function(property, newValue) {
		if (typeOf(property) == 'string') {
			property = this.ticketPage.getPropertyManager(property);
		}
		
		property.setValue(newValue);
		property.pulseInterfaceElement();
	}
});