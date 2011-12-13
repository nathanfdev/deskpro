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
	}
};
DP.init();
