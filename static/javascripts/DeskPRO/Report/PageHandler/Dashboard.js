Orb.createNamespace('DeskPRO.Report.PageHandler');

/**
 * Dashboard page handler
 *
 * A dashboard has a number of widgets
 */ 
DeskPRO.Report.PageHandler.Dashboard = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,
	
	// List of the dashboard widgets
	widgets: [],
	
	// State of the dashboard, can be in view or edit state
	is_edit_state: false,
	
	initialize: function() {
	},

	initPage: function() {
		var self = this;
	},
	
	// Add a widget to the dashboard.
	// pos start index at 0
	addWidget: function(widget, pos) {
		
		// TODO: update widget state at server
		
		pos = pos || -1;
		
		// No position specified, add it to the end
		if (pos === -1) {
			this.widgets.push(widget);
		}
		else {
			// Need to insert at postion
			this.widgets.splice(pos, 0, widget)
		}
		
	},
	
	// Remove a widget from the dashboard.
	removeWidget: function(pos) {
		
		// TODO: update widget state at server
		
		if (this.widgets[pos])
			this.widgets.splice(pos, 1);
			
	},
	
	setEditable: function() {
		
		if (editable) {
			// Switch dashbaord to edit state
			this.updateToEditable();
		}
		else {
			// Switch dashbaord to view stat
			this.updateToViewable();
		}
	},
	
	updateToEditable: function() {
                
                this.is_edit_state = true;
                
        },
        
        updateToViewable: function() {
                
                this.is_edit_state = false;
                
        },
	
});