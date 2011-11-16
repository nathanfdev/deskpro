Orb.createNamespace('DeskPRO.Report.Chart.Base');

/**
 * Represents a Chart
 */
DeskPRO.Report.Chart.Base.Basic = new Orb.Class({

	// HTML element ID to write chart to
	initialize: function(element_id, dashboard_stat_id, options) {

		this.element_id        = element_id;
		this.dashboard_stat_id = dashboard_stat_id;
		this.options           = options || {};
	},

	onRenderComplete: function() {
	},

});