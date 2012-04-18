Orb.createNamespace('DeskPRO.Report.Chart.DeskPRO');

/**
 * Represents a Simple Variation Change Chart
 */
DeskPRO.Report.Chart.DeskPRO.SimpleVariation = new Orb.Class({
	Extends: DeskPRO.Report.Chart.DeskPRO.Base,

	initialize: function(element_id, dashboard_stat_id, vendor, vendor_type, options) {
		var self = this;

		this.parent(element_id, dashboard_stat_id, vendor, vendor_type, options);

		this.chart_type = 'simple-variation';

		this.sparkline_data = [];

		// TODO handle resize without element resize monitor
		$('#' + this.element_id).on('resize', function() {
			$('#' + self.element_id + ' .sparkline').sparkline(self.sparkline_data, { width: '100%', height: '30px'});
		});
	},

	onRenderComplete: function() {
		// Store the sparkline data
		this.sparkline_data = ($('#' + this.element_id + ' .sparkline').html()).split(",");

		$('#' + this.element_id + ' .sparkline').sparkline(this.sparkline_data, { width: '100%', height: '30px'});
	},
});