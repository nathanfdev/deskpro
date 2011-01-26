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
	initialize: function(ticketPage) {
		this.ticketPage = ticketPage;
		this.ticketId   = ticketPage.getMetaData('ticket_id');
		this.updateUrl  = ticketPage.getMetaData('saveActionsUrl');
	},
	
	
	/**
	 * Add a change to the set of changes
	 */
	addChange: function(property, newValue, applyNow) {
		
		if (property.isSameValue(newValue)) {
			return;
		}
		
		this.mode = 'multi';
		this.changes[property.getName()] = [property, newValue];
		
		if (applyNow) {
			this.applyChangeForProperty(property, newValue);
		}
	},
	
	
	
	/**
	 * Apply a certain new value in the interface
	 */
	applyChangeForProperty: function (property, newValue) {
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
		
		this.mode = 'single';
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
		this._addPropertyValueToData(data, property.getName(), property.getValue());
		
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
		
		Object.each(this.changes, function (change) {
			var property = change[0];
			var name = property.getName();
			
			this._addPropertyValueToData(data, property.getName(), property.getValue());
			property.unhighlightInterfaceElement();
		}, this);
		
		this.mode = 'single';
		
		$.ajax({
			type: 'POST',
			url: this.updateUrl,
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				
				this.changes = {};
				this.oldValues = {};
				
				if (data && typeOf(data) == 'object') {
					Object.each(data, function (returnValue, type) {
						var property = this.ticketPage.getPropertyManager(type);
						property.setIncomingValue(returnValue);
					}, this);
				}
			}
		});
	},
	
	_addPropertyValueToData: function(data, name, propertyValue) {
		
		// An array of items
		if (typeOf(propertyValue) == 'array') {
			for (var x = 0; x < propertyValue.length; x++) {
				var val = propertyValue[x];
				
				// Looks like its already a k:v like from serializeArray
				if (typeOf(val) == 'object' && val.name !== undefined) {
					data.push({
						name: 'actions['+name+']['+val.name+']',
						value: val.value
					});
				
				// We'll just make it an array of values then
				} else {
					data.push({
						name: 'actions['+name+']',
						value: val
					});
				}
			}

		// A k:v pair of items
		} else if (typeOf(propertyValue) == 'object') {
			Object.each(propertyValue, function(v, k) {
				data.push({
					name: 'actions['+name+']['+k+']',
					value: v
				});
			}, this);
			
		// A single value
		} else {
			data.push({
				name: 'actions['+name+']',
				value: propertyValue
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
		
		property.setIncomingValue(newValue);
		property.pulseInterfaceElement();
	}
});