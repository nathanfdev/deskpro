Orb.createNamespace('DeskPRO.Report.Chart.DeskPRO');

/**
 * Represents a Base DeskPRO chart
 */
DeskPRO.Report.Chart.DeskPRO.Base = new Orb.Class({
	Extends: DeskPRO.Report.Chart.Base.Basic,

	initialize: function(element_id, dashboard_stat_id, options) {

		this.parent(element_id, dashboard_stat_id, options);
	},

	render: function() {
		var self = this;

		// Load the chart
		$.ajax({
			url: DeskPRO_Window.getUrl('report_chart_get', {dashboard_stat_id: this.dashboard_stat_id}),
			type: 'GET',
			success: function(data) {
				$('#' + self.element_id).html(data);
				self.onRenderComplete();
			}
		});
	},

});