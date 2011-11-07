Orb.createNamespace('DeskPRO.Report.Chart.AmChart');

/**
 * Represents a Bar Chart
 */
DeskPRO.Report.Chart.AmChart.Bar = new Class({
        Extends: DeskPRO.Report.Chart.AmChart.Base,
	
	initialize: function() {
		
		this.chart_type = 'bar';
		
	},
});