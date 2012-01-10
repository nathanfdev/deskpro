if (!DP_DEBUG) DP_DEBUG = false;

var DP = {
	console: {
		error: function() {},
		log: function() {},
		warn: function() {},
		info: function() {},
		debug: function() {}
	},

	init: function() {
		if (!DP_DEBUG) {
			['error', 'log', 'warn', 'info', 'debug'].each(function(v) {
				if (window.console[v] && typeof window.console[v] == 'function') {
					var fn = window.console[v];
					DP.console[v] = function() {
						var args = Array.prototype.slice.call(arguments);
						var ret = fn.apply(window.console, args);
						return ret;
					}
				}
			});
		}

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
			theme_advanced_path: false
		};

		options = Object.merge(defaultOptions, options || {});

		return field.tinymce(options);
	}
};
DP.init();
