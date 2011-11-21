Orb.createNamespace('DeskPRO.Report.Chart.DeskPRO');

/**
 * Represents a Simple Variation Change Chart
 */
DeskPRO.Report.Chart.DeskPRO.SimpleDrillDown = new Orb.Class({
	Extends: DeskPRO.Report.Chart.DeskPRO.Base,

	initialize: function(element_id, dashboard_stat_id, vendor, vendor_type, options) {
		this.parent(element_id, dashboard_stat_id, vendor, vendor_type, options);

		this.chart_type = 'simple-drill-down';
	},	
});