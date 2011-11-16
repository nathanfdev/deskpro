Orb.createNamespace('DeskPRO.Report.PageHandler');

/**
 * Dashboard page handler
 *
 * A dashboard has a number of widgets
 */
DeskPRO.Report.PageHandler.Dashboard = new Orb.Class({
	Extends: DeskPRO.Report.PageHandler.Basic,

	initialize: function(dashboard_id, options) {

		options = options || {};

		// Id of the dashboard
		this.dashboard_id = dashboard_id;

		// List of the dashboard widgets
		this.widgets = [];

		// State of the dashboard, can be in view or edit state
		this.is_edit_state = false;

		// Number of columns in the dashboard grid
		this.number_columns = options.number_columns || 4;

		// The width of 1 columns
		this.column_width = null;

		// The absolute minimum a column can be resized to (px)
		this.min_column_width = 250;

		// Spacing between widgets in the columns [top, right, bottom, left]
		this.column_spacing = [0, 5, 10, 5];

		// Time for animation of widget resize to take place
		this.animation_duration = 100;

		// UI Overlay
		this.overlay = null;

		// The supported vendor namespaces
		this.supported_vendors = ['AmChart', 'DeskPRO'];

		// The supported chart Classes
		this.supported_charts = ['Column', 'Line', 'Pie', 'SimpleVariation', 'SimpleDrillDown', 'DetailedDrillDown'];

		// Refernce to the dashboard
		this.$dashboard = $("#report-dashboard");

		// Reference to dashboard grid
		this.$dashboardGrid = $("#report-dashboard-grid");
	},

	// Init the page
	initPage: function() {
		var self = this;

		this.setMinPageWidth();

		// Setup some templates
		$('#dashboard_widget').template('dashboard_widget');
		$('#dashboard_widget_create').template('dashboard_widget_create');
		$('#dashboard_widget_select').template('dashboard_widget_select');
		$('#dashboard_widget_edit').template('dashboard_widget_edit');

		// Create the dashboard overlay
		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#overlay_wrapper')
		});
		$('#overlay_wrapper .close-overlay').on('click', function() {
			self.overlay.close();
		});

		// Setup the grid option slider
		$("#report-dashboard-options-num-columns-slider").slider({
			range: "max",
			min: 1,
			max: 4,
			value: this.number_columns,
			slide: function(event, ui) {
				self.number_columns = ui.value;

				self.calculateColumnWidth();
				self.resizeAllWidgets();
			},
			stop: function(event, ui) {
				// Need to re render the widgets - Flash charts will do
				// this for us, JS ones dont seem to support it
				//self.renderWidgets();
			}
		});

		$('#report-dashboard-set-editable').on('click', function() {
			self.setEditable(true);
			return false;
		});

		$('#report-dashboard-set-viewable').on('click', function() {
			self.setEditable(false);
			return false;
		});

		// Need to ensure dashboard update correctly if window size changes
		this.$dashboardGrid.on('resize', function() {
			self.calculateColumnWidth();
			self.resizeAllWidgets();
		});

		// Calculate initial dashboard column width, grab the widgets
		this.calculateColumnWidth();
		this.fetchWidgets();
	},

	// Open the overlay loading in a template
	openOverlay: function(overlay_content) {

		$('.overlay-content').html(overlay_content);
		this.overlay.open();

	},

	// Clean the overlay and close it
	closeOverlay: function() {

		$('.overlay-content').html('');
		this.overlay.close();

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
					self.createWidgetFromJSON(v);
				});

				self.calculateColumnWidth();
				self.resizeAllWidgets();

				self.renderWidgets();
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
				self.createWidgetFromJSON(data);
			}
		});
	},

	// Create a widget from a JSON response
	createWidgetFromJSON: function(data) {

		var widget = new DeskPRO.Report.Dashboard.Widget(this, data.id, data.stat);
		this.addWidget(widget, data.grid_slots);

		widget.setContent(this.createChart(
			data.chart_vendor,
			data.chart_class,
			"widget_content_" + widget.element_id,
			data.id)
		);
	},

	// Create a chart, can be any number of vendors of chart types
	createChart: function(vendor, chart_type, chart_element_id, dashboad_stat_id) {

		if (this.supported_vendors.indexOf(vendor) == -1) {
			throw "Unsupported Vendor type [" + vendor + "]";
		}

		if (this.supported_charts.indexOf(chart_type) == -1) {
			throw "Unsupported Chart Class [" + chart_type + "]";
		}

		var chartClass = eval("DeskPRO.Report.Chart." + vendor + "." + chart_type);

		return new chartClass(chart_element_id, dashboad_stat_id);
	},

	// Render the widget content. Need to do this when widget sizes change as
	// widget content may need to redraw itself
	renderWidgets: function() {

		Array.each(this.widgets, function(v) {
			v.widget.getContent().render();
			v.widget.hideLoader();
		});

	},

	// Add a widget to the dashboard.
	// pos start index at 0
	addWidget: function(widget, num_slots, pos) {

		pos = pos || -1;
		num_slots = num_slots || 1;

		var insert_widget = {
			widget: widget,
			num_slots: num_slots
		};

		// No position specified, add it to the end
		if (pos === -1) {
			this.widgets.push(insert_widget);
			pos = this.widgets.length - 1;
		}
		else {
			// Need to insert at postion
			this.widgets.splice(pos, 0, insert_widget)
		}

		// Add widget to UI
		this.$dashboardGrid.append($.tmpl('dashboard_widget', {widget: widget}));
		widget.addUIHandlers();

		// Setup the spacing
		this.applySpacingToElements($('.widget'));
	},

	// Removes a widget from the dashboard
	deleteWidget: function(element_id) {
		var self         = this;
		var widget_index = this.getWidgetIndexById(element_id);
		var widget       = this.widgets[widget_index];

		// Destroy the widget
		$.ajax({
			url: DeskPRO_Window.getUrl('report_dashboard_ajaxdeletewidget', {dashboard_id: this.dashboard_id, dashboard_stat_id: widget.widget.widget_id}),
			dataType: 'json',
			type: 'GET',
			success: function(data) {
				self.$dashboard.find('li#' + element_id).remove();
				self.widgets.splice(widget_index, 1);
			}
		});

	},

	// Set dashboard state, can be editable or viewable
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
		var self = this;

		// Hide the edit link, show the view link
		$("#report-dashboard-set-editable").css('display', 'none');
		$("#report-dashboard-set-viewable").css('display', 'block');
		$("#report-dashboard-options").css('display', 'block');

		// Create the 'Add Widget' placeholder
		this.createAddChartPlaceholder();

		// Make the dashboard widgets sortable
		this.$dashboardGrid.sortable({
			handle: '.grid-slot-toolbar',
			items:  "li:not(#dashboard-new-placeholderd)"
		});
		this.$dashboardGrid.disableSelection();

		// Make the dashboard widgets resizable
		this.$dashboardGrid.find("li.chart-widget").resizable({
			helper: "ui-resizable-helper",
			handles: 'e',
			distance: 40,
			start: function(event, ui) {
				var html = '<div class="resize-overlay resize-left"></div>';
				$(this).find('.ui-resizable-helper').append(html);
			},
			resize: function(event, ui) {
				// Prevent height resize
				ui.size.height = ui.originalSize.height;
			},
			stop: function(event, ui) {
				// Remove the resize overlay
				$(this).find('.resize-overlay').remove();

				// TODO: remove this when window resize event handler is working
				self.calculateColumnWidth();

				var closest_column_size = self.calculateClosestColumnSize(ui.size.width);
				var widget_index = self.getWidgetIndexById(ui.element.attr('id'));

				// Adjust the resized widget to the closest column
				self.resizeWidgetToColumn(widget_index, closest_column_size, true);
			}
		});

		// Set each widget as editable
		Array.each(this.widgets, function(v) {
			v.widget.setEditable(true);
		});

		this.calculateColumnWidth();

		this.is_edit_state = true;
	},

	// Update UI state to viewable
	updateToViewable: function() {

		// Save the new state of the dashboard
		this.saveDashboardState();

		// Hide the view link, show the edit link
		$("#report-dashboard-set-viewable").css('display', 'none');
		$("#report-dashboard-set-editable").css('display', 'block');
		$("#report-dashboard-options").css('display', 'none');

		// Remove the add placeholder
		this.removeAddChartPlaceholder();

		// Remove the sortable and resizable functionality
		this.$dashboardGrid.sortable('destroy');
		this.$dashboardGrid.find("li").resizable('destroy');

		// Set each widget back to viewable
		Array.each(this.widgets, function(v) {
			v.widget.setEditable(false);
		});

		this.is_edit_state = false;
	},

	// Add UI handlers for the add chart overlay
	addAddChartUIHandlers: function() {

		$('#dashboard_widget_select a.add-chart').on('click', function() {
			var href = $(this).attr('href');
			$.ajax({
				url: href,
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					$('.overlay-content').html(data.html);
				}
			});

			return false;
		});

	},

	// Save the state of the dashboard
	saveDashboardState: function() {
		var self = this;

		dashboardState = { widgets: [], number_columns: this.number_columns };

		// Get the state of each of the dashboard widgets
		this.$dashboardGrid.find("li.chart-widget").each(function(i, el) {
			var widget_index = self.getWidgetIndexById($(this).attr('id'));
			var v = self.widgets[widget_index];

			dashboardState.widgets.push({id: v.widget.widget_id, number_columns: v.num_slots, slot_number: i})
		});

		$.ajax({
			url: DeskPRO_Window.getUrl('report_dashboard_ajaxsavedashboardstate', {dashboard_id: this.dashboard_id}),
			data: 'dashboard_state=' + JSON.stringify(dashboardState),
			dataType: 'json',
			type: 'POST',
			success: function(data) {
				// Check stats return
			}
		});
	},

	// Create the placeholder for the add chart widget
	createAddChartPlaceholder: function() {
		var self = this;

		this.$dashboardGrid.append($.tmpl('dashboard_widget_create'));

		this.applySpacingToElements($('#dashboard-new-placeholder'));

		// Ensure widget is the correct size
		this.resizePlacerHolderWidget();

		// Set handler to process click events, we want to display an overlay
		$("#dashboard-new-placeholder-link").on('click', function() {
			self.openOverlay($.tmpl('dashboard_widget_select'));
			self.addAddChartUIHandlers();

			return false;
		});
	},

	// Remove the placeholder
	removeAddChartPlaceholder: function() {

		// Remove the 'Add Widget' placeholder
		$('#dashboard-new-placeholder').remove();

	},

	// Get a widget object by it element id
	getWidgetIndexById: function(element_id) {
		var index      = 0;
		var foundIndex = 0;

		Array.each(this.widgets, function(v) {
			if (v.widget.element_id === element_id) {
				foundIndex = index;
			}
			index++;
		});

		return foundIndex;
	},

	// Resize all the widgets
	resizeAllWidgets: function() {
		var self  = this;
		var index = 0;

		Array.each(this.widgets, function(v) {
			self.resizeWidgetToColumn(index, v.num_slots, false);
			index++;
		});

		// Resize the placeholder is we are in edit state
		if (this.is_edit_state === true) {
			this.resizePlacerHolderWidget()
		}

	},

	// Resize a widget to fit into number_columns
	resizeWidgetToColumn: function(widget_index, number_columns, animate) {

		// We need to shrink widgets that maybe to big if the column count
		// in the dashboard has been reduced
		if (number_columns > this.number_columns) {
			// Widget cannot be bigger than the number of columns available
			number_columns = this.number_columns;
		}

		// Update the column size for this widget
		this.widgets[widget_index].num_slots = number_columns;

		var new_width = this.calculateWidthOfWidgetByColumnCount(number_columns);

		// Do the resize, we may want to animate
		if (animate) {
			$('#' + this.widgets[widget_index].widget.element_id).animate({
				width: new_width + 'px'
			}, this.animation_duration);
		} else {
			$('#' + this.widgets[widget_index].widget.element_id).css('width', new_width + 'px');
		}

	},

	// Resize the placeholder widget
	resizePlacerHolderWidget: function() {

		// Always takes up 1 column in width
		var new_width = this.calculateWidthOfWidgetByColumnCount(1);
		$('#dashboard-new-placeholder').css('width', new_width + 'px');

	},

	// Apply the spacing to an element group
	applySpacingToElements: function($elements) {

		$elements.css('margin-top', this.column_spacing[0] + 'px');
		$elements.css('margin-right', this.column_spacing[1] + 'px');
		$elements.css('margin-bottom', this.column_spacing[2] + 'px');
		$elements.css('margin-left', this.column_spacing[3] + 'px');

	},

	// Setup the jQuery resizable grid
	setupResizableGrid: function() {

		this.$dashboard.find("li").resizable("option", "grid", [5, 50]);
		this.$dashboard.find("li").resizable("option", "minWidth", this.column_width);
		this.$dashboard.find("li").resizable("option", "maxWidth", this.getDashboardWidth());

	},

	// Calculate the closets column size from a width
	calculateClosestColumnSize: function(width) {

		// Calculate to a half column - will snap up or down depending
		// which side of the half column the user resizes to
		var half_column = this.column_width / 2;

		var column_size = 1;
		for (var i = 0; i <= this.number_columns; i++) {
			if (width < (this.column_width * i) + half_column) {
				column_size = i;
				break;
			}
		}

		return column_size;
	},

	// Calculate the width of 1 column
	calculateColumnWidth: function() {

		this.column_width = (this.getDashboardWidth() - this.getTotalSpacerWidth()) / this.number_columns;
		if (this.column_width < this.min_column_width) {
			this.column_width = this.min_column_width;
		}

		this.setupResizableGrid();
	},

	// Calculate the width of a widget by the number of columns it takes up
	calculateWidthOfWidgetByColumnCount: function(size) {

		return (this.column_width * size) + (this.getWidgetSpacerWidth() * (size - 1));

	},

	// Get the board width. The last widget in a row shouldn't have any right
	// spacing, but as it does for now, we need to reduce this dashboard size
	// by this amount
	getDashboardWidth: function() {

		// Last widget doesn't need to have spacing on the right
		return this.$dashboard.width() - this.getWidgetSpacerWidth();

	},

	// Get the spacer size for width
	getWidgetSpacerWidth: function() {

		return (this.column_spacing[1] + this.column_spacing[3]);

	},

	// Get the total spacer size for entire row in dashboard
	getTotalSpacerWidth: function() {

		// Last column does not have a spacer on it
		return this.getWidgetSpacerWidth() * (this.number_columns - 1);

	},

	// Set the minimum page widht
	setMinPageWidth: function() {

		$('body').css('min-width', '1240px');

	}

});
