Orb.createNamespace('DeskPRO.Report.Chart.AmChart');

/**
 * Represents a Bar Chart
 */
DeskPRO.Report.Chart.AmChart.Base = new Orb.Class({
	Extends: DeskPRO.Report.Chart.Base.Basic,

	initialize: function(element_id, dashboard_stat_id, vendor, vendor_type, options) {

		this.parent(element_id, dashboard_stat_id, vendor, vendor_type, options);

		// The type of charts to support
		// 3 options:
		//      fallback: Tries Flash, otherwise falls back to javascript
		//      javascript: JavaScript only (if not supported charts will fail)
		//      flash: Flash only
		this.support_mode = this.options.support_mode || 'fallback';

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

		var params = {
			bgcolor:"#FFFFFF"
		};

		var setting_url = DeskPRO_Window.getUrl('report_chart_get_settings', {dashboard_stat_id: this.dashboard_stat_id});
		if (this.chart_type_index >= 0) {
			setting_url += '?chart_type=' + this.chart_type_index + '&all=true';
		}

		var vars = {
			path: "/web/vendor/amcharts/flash/",

			settings_file: setting_url
		};

		if ((this.support_mode == 'fallback' || this.support_mode == 'flash') &&
			swfobject.hasFlashPlayerVersion("8"))
		{
			swfobject.embedSWF(ASSETS_BASE_URL+"/vendor/amcharts/flash/am" + this.chart_type + ".swf", this.element_id, "100%", "100%", "8.0.0", ASSETS_BASE_URL+"/vendor/amcharts/flash/expressInstall.swf", vars, params);
		}
		else if (this.isJavaScriptSupported()) {
			this.chart = new AmCharts.AmFallback();
			this.chart.settingsFile = vars.settings_file;
			this.chart.pathToImages = ASSETS_BASE_URL+"/vendor/amcharts/javascript/images/";
			this.chart.type = this.chart_type;
			this.chart.write(this.element_id);
		}

	},

	// Checks if JavaScript is available
	isJavaScriptSupported: function() {

		return AmCharts.recommended() == "js";

	}
});
