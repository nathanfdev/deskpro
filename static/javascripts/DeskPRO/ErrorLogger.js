var DpErrorLog = {
	_origHandler: null,
	saveUrl: null,
	init: function() {
		if (!this.saveUrl) {
			return;
		}

		if (!window.console || !window.console.firebug) {

		}

		window.onerror = this.handleError;
		if (window.onerror) {
			this._origHandler = window.onerror;
		}

		if (window.console.error) {
			var oldConsole = window.console.error;
			window.console.error = function(msg) {
				DpErrorLog.logError(msg + " " + printStackTrace().join(", "));
				oldConsole.apply(oldConsole, arguments);
			}
		}
	},

	logError: function(message) {
		$.ajax({
			url: this.saveUrl,
			data: {
				message: message
			},
			type: 'POST'
		});

		console.log('[JS Error] %s', message);
	},

	handleError: function(message, script, line) {
		if (!message.length || !script.length) {
			return false;
		}

		DpErrorLog.logError(message + ' (' + script + ' on line ' + line + ')');

		if (this._origHandler) {
			this._origHandler.apply(this._origHandler, arguments);
		}

		return true;
	}
};
