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
		this.dashboard_id = dashboard_id;
		
		this.$dashboard 	= $("#report-dashboard");
		this.$dashboardGrid 	= $("#report-dashboard-grid");
		
		$('#dashboard_widget').template('dashboard_widget');
		$('#dashboard_widget_create').template('dashboard_widget_create');
	},

	initPage: function() {
		var self = this;
		
		$("#report-dashboard-options-num-columns-slider").slider({
			range: "max",
			min: 1,
			max: 8,
			value: 4,
			slide: function(event, ui) {
				self.number_columns = ui.value;
				
				self.calculateColumnWidth();
				self.setupResizableGrid();
				self.loadCharts();
			}
		});
		
		$('#report-dashboard-set-editable').click(function() {
			self.setEditable(true);
			return false;
		});
		
		$('#report-dashboard-set-viewable').click(function() {
			self.setEditable(false);
			return false;
		});
		
		$(window).resize(function() {
			self.calculateColumnWidth();
			self.setupResizableGrid();
		});
		
		
		this.fetchWidgets();
	},
	
	// Fetch the widgets
	fetchWidgets: function() {
		var self = this;
		$.ajax({
			url: DeskPRO_Window.getUrl('report_dashboard_ajaxfetchwidgets', {dashboard_id: this.dashboard_id}),
			dataType: 'json',
			type: 'GET',
			success: function(data) {
				Array.each(data.widgets, function(v) {
					console.log( v.stat)
					var widget = new DeskPRO.Report.Dashboard.Widget(v.id, v.stat);
					self.addWidget(widget);
				});
				self.calculateColumnWidth();
			}
		});
	},
	
	// Create a widget and fetch it
	createAndFetchWidget: function() {
		var self = this;
		$.ajax({
			url: DeskPRO_Window.getUrl('report_dashboard_ajaxcreatewidget', {dashboard_id: this.dashboard_id}),
			dataType: 'json',
			type: 'GET',
			success: function(data) {
				var widget = new DeskPRO.Report.Dashboard.Widget(data.id, data.stat);
				self.addWidget(widget);
			}
		});
	},
	
	// Add a widget to the dashboard.
	// pos start index at 0
	addWidget: function(widget, pos) {
		
		// TODO: update widget state at server
		
		pos = pos || -1;
		
		var insert_widget = {
			widget: widget,
			num_slots: 1
		};
		
		// No position specified, add it to the end
		if (pos === -1) {
			this.widgets.push(insert_widget);
		}
		else {
			// Need to insert at postion
			this.widgets.splice(pos, 0, insert_widget)
		}
		
		// Add widget to UI
		this.$dashboardGrid.append($.tmpl('dashboard_widget', {widget: widget}));
		$('.widget').css('margin-right', this.column_spacing[1] + 'px');
	},
	
	// Remove a widget from the dashboard.
	removeWidget: function(pos) {
		
		// TODO: update widget state at server
		
		if (this.widgets[pos])
			this.widgets.splice(pos, 1);
			
	},
	
	setEditable: function(editable) {
		
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
                
		// Hide the edit link, show the view link
		$("#report-dashboard-set-editable").css('display', 'none');
		$("#report-dashboard-set-viewable").css('display', 'block');
		$("#report-dashboard-options").css('display', 'block');
		
		// Create the 'Add Widget' placeholder
		this.$dashboardGrid.append($.tmpl('dashboard_widget_create'));
		
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
		
		Array.each(this.widgets, function(v) {
			v.widget.setEditable(true);
		});
		
		this.setupResizableGrid();
		
                this.is_edit_state = true;
                
        },
        
	// Update UI state to viewable
        updateToViewable: function() {
                
		// Hide the view link, show the edit link
		$("#report-dashboard-set-viewable").css('display', 'none');
		$("#report-dashboard-set-editable").css('display', 'block');
		$("#report-dashboard-options").css('display', 'none');
		
		// Remove the 'Add Widget' placeholder
		$('#dashbard-new-placeholder').remove();
		
		this.$dashboardGrid.sortable('destroy');
		this.$dashboardGrid.find("li").resizable('destroy');
		
		Array.each(this.widgets, function(v) {
			v.widget.setEditable(false);
		});
		
                this.is_edit_state = false;
                
        },
	
	// Set the number of columns and update UI to reflect this
	setNumberColumns: function(num_columns) {
		
		this.number_columns = num_columns;
		
	},
	
	// Calculate the width of a columns	
	calculateColumnWidth: function() {
		var self = this;
		this.column_width = (this.getDashboardWidth() - this.getTotalSpacerWidth()) / this.number_columns;
		
		Array.each(this.widgets, function(v) {
			$("#" + v.widget.element_id).css('width', (v.num_slots * self.column_width) + 'px');
		});
		
		$("#dashbard-new-placeholder").css('width', self.column_width + 'px');
	},
	
	setupResizableGrid: function() {
		
		var snapSizeX  = (this.getDashboardWidth() - this.getTotalSpacerWidth()) / this.number_columns;
		snapSizeX += this.getWidgetSpacerWidth() * 
		this.$dashboard.find("li").resizable("option", "grid", [snapSizeX, 50]);
		this.$dashboard.find("li").resizable("option", "minWidth", snapSizeX);
	},
	
	loadCharts: function() {
		
	},
	
	getDashboardWidth: function() {
		
		return this.$dashboard.width();
		
	},
	
	getWidgetSpacerWidth: function() {
		
		return (this.column_spacing[1] + this.column_spacing[3]);
		
	},
	
	getTotalSpacerWidth: function() {
		
		return this.getWidgetSpacerWidth() * this.number_columns;
		
	}
	
});