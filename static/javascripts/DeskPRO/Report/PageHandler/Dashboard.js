Orb.createNamespace('DeskPRO.Report.PageHandler');

/**
 * Dashboard page handler
 *
 * A dashboard has a number of widgets
 */
DeskPRO.Report.PageHandler.Dashboard = new Orb.Class({
	Extends: DeskPRO.Report.PageHandler.Basic,

	initialize: function(dashboard_id) {
		this.dashboard_id = dashboard_id;

		// Id of dashboard
		this.dashboard_id = null;

		// List of the dashboard widgets
		this.widgets = [];

		// State of the dashboard, can be in view or edit state
		this.is_edit_state = false;

		// Number of columns in the dashboard grid
		this.number_columns = 4;

		// The width of 1 columns
		this.columns_width = null;

		// Spacing between widgets in the columns [top, right, bottom, left]
		this.column_spacing = [0, 10, 10, 0];

		// UI Overlay
		this.overlay = null;

		// The supported vendor namespaces
		this.supported_vendors = ['AmChart'];

		// The supported chart Classes
		supported_charts = ['Column', 'Line'];

		// Refernce to the dashboard
		this.$dashboard = $("#report-dashboard");

		// Reference to dashboard grid
		this.$dashboardGrid = $("#report-dashboard-grid");

		$('#dashboard_widget').template('dashboard_widget');
		$('#dashboard_widget_create').template('dashboard_widget_create');
		$('#dashboard_widget_select').template('dashboard_widget_select');
		$('#dashboard_widget_edit').template('dashboard_widget_edit');
	},

	initPage: function() {
		var self = this;

		this.overlay = new DeskPRO.UI.Overlay({
                        contentElement: $('#overlay_wrapper')
                });

		$("#report-dashboard-options-num-columns-slider").slider({
			range: "max",
			min: 1,
			max: 8,
			value: 4,
			slide: function(event, ui) {
				self.number_columns = ui.value;

				self.calculateColumnWidth();
				self.setupResizableGrid();
			},
			stop: function(event, ui) {
				self.renderCharts();
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

	// Open the overlay loading in a template
	openOverlay: function(template_id) {

		$('.overlay-content').html($.tmpl(template_id));
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

				self.renderCharts();
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

		var widget = new DeskPRO.Report.Dashboard.Widget(self, data.id, data.stat);
		this.addWidget(widget);

		widget.setChart(this.createChart(
					data.chart_vendor,
					data.chart_class,
					"chart_" + widget.element_id,
					data.id)
				);
	},

	createChart: function(vendor, chart_type, chart_element_id, dashboad_stat_id) {

		if (this.supported_vendors.indexOf(vendor) == -1) {
			throw "Unsupported Vendor type [" + vendor + "]";
		}

		if (this.supported_charts.indexOf(chart_type) == -1) {
			throw "Unsupported Chart Class [" + chart_type + "]";
		}

		var chartClass = eval("DeskPRO.Report.Chart." + vendor + "." + chart_type);

		var chart  = new chartClass(chart_element_id, dashboad_stat_id);

		return chart;
	},

	renderCharts: function() {

		Array.each(this.widgets, function(v) {
			v.widget.getChart().renderChart();
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
		widget.addUIHandlers();

		$('.widget').css('margin-right', this.column_spacing[1] + 'px');
	},

	// Remove a widget from the dashboard.
	removeWidget: function(widget) {

		// TODO: update widget state at server

		var pos = this.getWidgetIndexByElementId(widget.element_id);
		if (pos === -1) {
			return;
		}

		if (this.widgets[pos]) {
			this.widgets.splice(pos, 1);
		}
	},

	getWidgetIndexByElementId: function(element_id) {
		var index = 0;
		var pos   = -1;

		Array.each(this.widgets, function(v) {
			if (element_id === v.element_id) {
				pos = index;
			}

			index++;
		});

		return pos;
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
                var self = this;

		// Hide the edit link, show the view link
		$("#report-dashboard-set-editable").css('display', 'none');
		$("#report-dashboard-set-viewable").css('display', 'block');
		$("#report-dashboard-options").css('display', 'block');

		// Create the 'Add Widget' placeholder
		this.createAddChartPlaceholder();

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

		this.calculateColumnWidth();
		this.setupResizableGrid();

                this.is_edit_state = true;
        },

	// Update UI state to viewable
        updateToViewable: function() {

		// Hide the view link, show the edit link
		$("#report-dashboard-set-viewable").css('display', 'none');
		$("#report-dashboard-set-editable").css('display', 'block');
		$("#report-dashboard-options").css('display', 'none');

		this.removeAddChartPlaceholder();

		this.$dashboardGrid.sortable('destroy');
		this.$dashboardGrid.find("li").resizable('destroy');

		Array.each(this.widgets, function(v) {
			v.widget.setEditable(false);
		});

                this.is_edit_state = false;
        },

	createAddChartPlaceholder: function() {
		var self = this;

		this.$dashboardGrid.append($.tmpl('dashboard_widget_create'));
		$("#dashboard-new-placeholder-link").click(function() {
			self.openOverlay('dashboard_widget_select');

			$('.stat-list .add-chart').click(function() {
				var href = $(this).attr('href');
				$.ajax({
					url: href,
					dataType: 'json',
					type: 'GET',
					success: function(data) {
						self.removeAddChartPlaceholder();

						var widget = new DeskPRO.Report.Dashboard.Widget(self, data.widget.id, data.widget.stat);
						self.addWidget(widget);

						self.createAddChartPlaceholder();
					}
				});

				return false;
			});

			return false;
		});

	},

	removeAddChartPlaceholder: function() {

		// Remove the 'Add Widget' placeholder
		$('#dashboard-new-placeholder').remove();

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

		$("#dashboard-new-placeholder").css('width', self.column_width + 'px');
	},

	setupResizableGrid: function() {

		var snapSizeX  = (this.getDashboardWidth() - this.getTotalSpacerWidth()) / this.number_columns;

		this.$dashboard.find("li").resizable("option", "grid", [snapSizeX, 50]);
		this.$dashboard.find("li").resizable("option", "minWidth", snapSizeX);
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