Orb.createNamespace('DeskPRO.Report.Chart.Base');

/**
 * Represents a Chart
 */
DeskPRO.Report.Chart.Base.Basic = new Orb.Class({

	// HTML element ID to write chart to
	initialize: function(element_id, dashboard_stat_id, vendor, vendor_type, options) {

		this.element_id        = element_id;
		this.dashboard_stat_id = dashboard_stat_id;
		this.vendor	       = vendor;
		this.vendor_type       = vendor_type;
		this.options           = options || {};
		this.chart_type_index  = -1;
	},
	
	onRenderComplete: function() {
	},

});