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

	onRenderComplete: function() {
		var self = this;

		$('#' + this.element_id + ' td.trend').each(function() {
			self.renderSparklines();
		});

		// TODO handle resize without element resize monitor
		$('#' + this.element_id).resize(function() {
			$('#' + self.element_id + ' td.trend').html('');

			self.renderSparklines();
		});
	},

	renderSparklines: function() {

		$('#' + this.element_id + ' td.trend').each(function() {
			var trendData = $(this).attr('data-trendline').split(',');

			$(this).html('<span class="sparkline"></span>');
			$(this).find('.sparkline').sparkline(trendData, { width: '100%', height: '20px'});
		})
	},

});