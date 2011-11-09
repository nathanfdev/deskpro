Orb.createNamespace('DeskPRO.Report.Chart.AmChart');

/**
 * Represents a Bar Chart
 */
DeskPRO.Report.Chart.AmChart.Column = new Orb.Class({
	Extends: DeskPRO.Report.Chart.AmChart.Base,

	initialize: function() {
		this.chart_type = 'column';
	}
});