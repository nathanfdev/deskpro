Orb.createNamespace('DeskPRO.Report.Chart.AmChart');

/**
 * Represents a Bar Chart
 */
DeskPRO.Report.Chart.AmChart.Base = new Orb.Class({
	Extends: DeskPRO.Report.Chart.Base.Basic,

	initialize: function(element_id, dashboard_stat_id, options) {

		this.parent(element_id, dashboard_stat_id, options);

		// The type of charts to support
		// 3 options:
		//      fallback: Tries JavaScript, otherwise falls back to flash
		//      javascript: JavaScript only (if not supported charts will fail)
		//      flash: Flash only
		this.support_mode = this.options.support_mode || 'fallback';

		// Chart width (px)
		this.width  = this.options.width || 600;

		// Chart height (px)
		this.height = this.options.height || 400;

		// Instance of AM chart
		this.chart = null;

		this.chart_settings = '';

		this.chart_data = '';

		// Chart of chart to create
		this.chart_type = '';

		if (this.support_mode == 'javascript' && !this.isJavaScriptSupported()) {
			throw "Chart Error: Cannot use 'javascript' as a support_mode. Unsupported option";
		}
	},

	render: function() {

		var vars = {
			path: "/static/vendor/amcharts/flash/",

			settings_file: DeskPRO_Window.getUrl('report_chart_get_settings', {dashboard_stat_id: this.dashboard_stat_id}),
			data_file: DeskPRO_Window.getUrl('report_chart_get_data', {dashboard_stat_id: this.dashboard_stat_id})
		};

		if ((this.support_mode == 'fallback' || this.support_mode == 'javascript') &&
			this.isJavaScriptSupported())
		{
			this.chart = new AmCharts.AmFallback();
			this.chart.settingsFile = vars.settings_file;
			this.chart.dataFile = vars.data_file;
			this.chart.pathToImages = "/static/vendor/amcharts/javascript/images/";
			this.chart.type = this.chart_type;
			this.chart.write(this.element_id);
		}
		else {
			swfobject.embedSWF("/static/vendor/amcharts/flash/amline.swf", this.elementId, "600", "400", "8.0.0", "/static/vendor/amcharts/flash/expressInstall.swf", vars, params);
		}
	},

	// Checks if JavaScript is available
	isJavaScriptSupported: function() {

		return AmCharts.recommended() == "js";

	}
});