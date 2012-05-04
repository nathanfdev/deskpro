if (typeof DP_DEBUG == 'undefined' || !DP_DEBUG) DP_DEBUG = false;

var DP = {
	console: {
		error: function() {},
		log: function() {},
		warn: function() {},
		info: function() {},
		debug: function() {}
	},

	init: function() {
		if (typeof window.console != 'undefined') {
			DP.console = window.console;
		}
		['error', 'log', 'warn', 'info', 'debug'].each(function(v) {
			if (!DP.console[v]) {
				DP.console[v] = function() {};
			}
		});

		delete DP.init;
	},

	rteTextarea: function(field, options) {
		field = $(field);

		defaultOptions = {
			script_url: ASSETS_BASE_URL + '/vendor/tiny_mce/tiny_mce.js',

			skin : "o2k7",
			skin_variant : "silver",

			theme: 'advanced',
			plugins : "fullscreen",
			theme_advanced_buttons1: 'bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect',
			theme_advanced_buttons2: ',bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen',
			theme_advanced_buttons3: '',
			theme_advanced_toolbar_location: 'top',
			theme_advanced_toolbar_align: 'left',
			theme_advanced_resizing: true,
			theme_advanced_statusbar_location: 'bottom',
			theme_advanced_path: false,
			relative_urls: false
		};

		options = Object.merge(defaultOptions, options || {});

		return field.tinymce(options);
	},

	drawBox: function(w, h) {
		if (this.lastBox) {
			this.lastBox.remove();
			this.lastBox = null;
		}
		this.lastBox = $('<div style="background-color: #263343; position: absolute; width: '+w+'px; height: '+h+'px; z-index: 99999999;"></div>');

		var left = ($(window).width() / 2) - (w/2);
		var top  = ($(window).height() / 2) - (h/2);

		this.lastBox.css({left: left, top: top}).appendTo('body').show();
		return this.lastBox;
	},

	removeDrawnBox: function() {
		if (this.lastBox) {
			this.lastBox.remove();
			this.lastBox = null;
		}
	}
};
DP.init();
