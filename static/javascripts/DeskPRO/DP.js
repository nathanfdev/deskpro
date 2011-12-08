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
		if (DP_DEBUG) {
			['error', 'log', 'warn', 'info', 'debug'].each(function(v) {
				if (window.console[v]) {
					DP.console[v] = function() {
						return window.console[v].apply(window.console[v], arguments);
					}
				}
			});
		}

		delete DP.init;
	}
};
DP.init();
