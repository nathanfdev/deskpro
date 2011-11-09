Orb.createNamespace('DeskPRO.Report.Chart.AmChart');

/**
 * Represents a Line Chart
 */
DeskPRO.Report.Chart.AmChart.Line = new Orb.Class({
	Extends: DeskPRO.Report.Chart.AmChart.Base,

	initialize: function(element_id, dashboard_stat_id, options) {
		this.parent(element_id, dashboard_stat_id, options);

		this.chart_type = 'line';
	}
});