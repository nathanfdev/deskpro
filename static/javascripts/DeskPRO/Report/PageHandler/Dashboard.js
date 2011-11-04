Orb.createNamespace('DeskPRO.Report.PageHandler');

/**
 * Dashboard page handler
 *
 * A dashboard has a number of widgets
 */ 
DeskPRO.Report.PageHandler.Dashboard = new Class({
	Extends: DeskPRO.Report.PageHandler.Basic,
	
	// Id of dashboard
	dashboard_id: null,
	
	// List of the dashboard widgets
	widgets: [],
	
	// State of the dashboard, can be in view or edit state
	is_edit_state: false,
	
	// Number of columns in the dashboard grid
	number_columns: 4,
	
	// The width of 1 columns
	columns_width: null,
	
	// Spacing between widgets in the columns [top, right, bottom, left]
	column_spacing: [0, 10, 10, 0],
	
	// Refernce to the dashboard
	$dashboard: null,
	
	// Reference to dashboard grid
	$dashboardGrid: null,
	
	initialize: function(dashboard_id) {
		var self = this;
		
		this.dashboard_id = dashboard_id
		
	},

	initPage: function() {
		var self = this;
		
		this.$dashboard 	= $("#report-dashboard");
		this.$dashboardGrid 	= $("#report-dashboard-grid");
		
		$("#report-dashboard-options-form").submit(function() {
			
			self.calculateColumnWidth();
			self.setupResizableGrid();
			self.loadCharts();
			
			return false;
		});
		
		$(window).resize(function() {
			//self.setupResizableGrid();
		});
		
		this.calculateColumnWidth();
		this.fetchWidgets();
		
		self.updateToEditable();
	},
	
	// Fetch the widgets
	fetchWidgets: function() {
		return;
		var self = this;
		$.ajax({
			url: DeskPRO_Window.getUrl('report_dashboard_ajaxfetchwidgets', {dashboard_id: this.dashboard_id}),
			dataType: 'json',
			type: 'GET',
			success: function(data) {
				self.updateToEditable();
			}
		});
	},
	
	// Create a widget and fetch it
	createAndFetchWidget: function() {
		
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
	
	// Update UI state to editable
	updateToEditable: function() {
                
		this.$dashboardGrid.sortable({
			handle: '.grid-slot-toolbar'
		});
		this.$dashboardGrid.disableSelection();
		
		this.$dashboardGrid.find("li").resizable({
			helper: "ui-resizable-helper",
			placeholder: "ui-state-highlight",
			resize: function(event, ui) {
				ui.size.height = ui.originalSize.height;
			}
		});
		
		this.setupResizableGrid();
		
                this.is_edit_state = true;
                
        },
        
	// Update UI state to viewable
        updateToViewable: function() {
                
                this.is_edit_state = false;
                
        },
	
	// Set the number of columns and update UI to reflect this
	setNumberColumns: function(num_columns) {
		
		this.number_columns = num_columns;
		
	},
	
	// Calculate the width of a columns	
	calculateColumnWidth: function() {
		
		// TODO: need to consider column spacing
		this.column_width = this.getDashboardWidth() / this.number_columns;
		
	},
	
	setupResizableGrid: function() {
		
		var snapSizeX  = this.getDashboardWidth() / this.number_columns;
			
		this.$dashboard.find("li").resizable("option", "grid", [snapSizeX, 50]);
		this.$dashboard.find("li").resizable("option", "minWidth", snapSizeX);
	},
	
	loadCharts: function() {
		
	},
	
	getDashboardWidth: function() {
		
		return this.$dashboard.width();
	}
	
});