Orb.createNamespace('DeskPRO.Report.Dashboard');

/**
 * Represent a dashboard widget
 */
DeskPRO.Report.Dashboard.Widget = new Orb.Class({

	initialize: function(dashboard, widget_id, options) {

		// Reference back to dashboard
		this.dashboard = dashboard;

		// Id of the widget
		this.widget_id = widget_id;

		this.element_id = null;

		// Number of slot this widget takes up in the dashboard grid
		this.num_slots = 1;

		// State of the widget, can be in view or edit state
		this.is_edit_state = false;

		// The chart associated with the widget
		this.chart = null;

		// HTML element ID
		this.element_id = Orb.getUniqueId();

		this.stat_id = options.stat_id || '';
		this.title   = options.title || '';
	},

	addUIHandlers: function() {
		var self = this;

		$("#" + this.element_id + " .edit").click(function() {
			self.dashboard.openOverlay('dashboard_widget_edit');

			return false;
		});

		$("#" + this.element_id + " .close").click(function() {
			self.dashboard.removeWidget(self);
			return false;
		});
	},

	setEditable: function(editable) {

		if (editable) {
			// Switch widget to editable state
			this.updateToEditable();
		}
		else {
			// Switch widget to view state
			this.updateToViewable();
		}
	},

	updateToEditable: function() {

		$("#report-dashboard-grid li .grid-slot-toolbar .icons a").css('display', 'block');
		$("#report-dashboard-grid li .grid-slot-toolbar").css('cursor', 'move');

		this.is_edit_state = true;
	},

	updateToViewable: function() {

		$("#report-dashboard-grid li .grid-slot-toolbar .icons a").css('display', 'none');
		$("#report-dashboard-grid li .grid-slot-toolbar").css('cursor', 'auto');

		this.is_edit_state = false;
	},

	setChart: function(chart) {

		this.chart = chart;

	},

	getChart: function() {

		return this.chart;

	},

	removeChart: function() {

		this.chart = null;

	},

});