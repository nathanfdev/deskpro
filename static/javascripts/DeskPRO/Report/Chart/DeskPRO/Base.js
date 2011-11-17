Orb.createNamespace('DeskPRO.Report.Chart.DeskPRO');

/**
 * Represents a Base DeskPRO chart
 */
DeskPRO.Report.Chart.DeskPRO.Base = new Orb.Class({
	Extends: DeskPRO.Report.Chart.Base.Basic,

	initialize: function(element_id, dashboard_stat_id, vendor, vendor_type, options) {

		this.parent(element_id, dashboard_stat_id, vendor, vendor_type, options);
	},

	render: function() {
		var self = this;

		var url = DeskPRO_Window.getUrl('report_chart_get', {dashboard_stat_id: this.dashboard_stat_id});
		if (this.chart_type_index >= 0) {
			url += '?chart_type=' + this.chart_type_index + '&all=true';
		}
		// Load the chart
		$.ajax({
			url: url,
			type: 'GET',
			success: function(data) {
				$('#' + self.element_id).html(data);
				self.onRenderComplete();
			}
		});
	},

});