var DpErrorLog = {
	_origHandler: null,
	saveUrl: null,
	init: function() {
		if (!this.saveUrl) {
			return;
		}

		window.onerror = this.handleError;
		if (window.onerror) {
			this._origHandler = window.onerror;
		}

		if (window.console.error) {
			var oldConsole = window.console.error;
			window.console.error = function(msg) {
				DpErrorLog.logError(jsDump.parse(arguments) + "\n" + printStackTrace().join("\n"));
				oldConsole.apply(oldConsole, arguments);
			}
		}
	},

	logError: function(message) {

		if (ASSETS_BASE_URL) {
			var r = new RegExp(ASSETS_BASE_URL.escapeRegExp(), 'g');
			message = message.replace(r, '');
		}

		$.ajax({
			url: this.saveUrl,
			data: {
				message: message
			},
			error: function() { },// prevents DeskPRO_Window's global error handler from firing on error
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
