Orb.createNamespace('DeskPRO.Report.Chart.AmChart');

/**
 * Represents a Bar Chart
 */
DeskPRO.Report.Chart.AmChart.Column = new Orb.Class({
	Extends: DeskPRO.Report.Chart.AmChart.Base,

	initialize: function(element_id, dashboard_stat_id, options) {
		this.parent(element_id, dashboard_stat_id, options);

		this.chart_type = 'column';
	}
});