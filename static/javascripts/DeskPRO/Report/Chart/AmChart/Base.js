Orb.createNamespace('DeskPRO.Report.Chart.AmChart');

/**
 * Represents a Bar Chart
 */
DeskPRO.Report.Chart.AmChart.Base = new Orb.Class({
	Extends: DeskPRO.Report.Chart.Base.Basic,

	initialize: function(element_id, dashboard_stat_id, options) {

		this.element_id        = element_id;
		this.dashboard_stat_id = dashboard_stat_id;

		options = options || {};
		this.support_mode = options.support_mode || 'fallback';

		// Instance of AM chart
		this.chart = null;

		this.chart_settings = '';

		this.chart_data = '';

		// Chart of chart to create
		this.chart_type = '';

		// The type of charts to support
		// 3 options:
		//      fallback: Tries JavaScript, otherwise falls back to flash
		//      javascript: JavaScript only (if not supported charts will fail)
		//      flash: Flash only
		this.support_mode = 'fallback';

		// Chart width (px)
		this.width = 600;

		// Chart height
		this.height =  400;

		this.width  = options.width || 600;
		this.height = options.height || 400;

		if (this.support_mode == 'javascript' && !this.isJavaScriptSupported()) {
			throw "Chart Error: Cannot use 'javascript' as a support_mode. Unsupported option";
		}
	},

	renderChart: function() {

		var vars = {
			path: "../../amcharts/flash/",

			settings_file: DeskPRO_Window.getUrl('report_chart_get_settings', {dashboard_stat_id: this.dashboard_stat_id}),
			data_file: DeskPRO_Window.getUrl('report_chart_get_data', {dashboard_stat_id: this.dashboard_stat_id})
		};

		if ((this.support_mode == 'fallback' || this.support_mode == 'javascript') &&
			this.isJavaScriptSupported())
		{
			this.chart = new AmCharts.AmFallback();
			this.chart.settingsFile = vars.settings_file;
			this.chart.dataFile = vars.data_file;
			this.chart.pathToImages = "../../amcharts/javascript/images/";
			this.chart.type = this.chart_type;
			this.chart.write(this.element_id);
		}
		else {	// TODO, incorrect paths
			swfobject.embedSWF("../../amcharts/flash/amline.swf", this.elementId, "600", "400", "8.0.0", "../../amcharts/flash/expressInstall.swf", vars, params);
		}
	},

	// Checks if JavaScript is available
	isJavaScriptSupported: function() {

		return AmCharts.recommended() == "js";

	}
});