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
	},

	logError: function(message, trace, script, line) {

		if (!this.saveUrl) {
			return;
		}

		if (ASSETS_BASE_URL) {
			var r = new RegExp(ASSETS_BASE_URL.escapeRegExp(), 'g');
			message = message.replace(r, '');
		}

		var data = {
			message: message || '',
			trace:   trace   || '',
			script:  script  || '',
			line:    line    || '0'
		};

		$.ajax({
			url: this.saveUrl,
			data: data,
			error: function() { },// prevents DeskPRO_Window's global error handler from firing on error
			type: 'POST'
		});

		DP.console.log('[JS Error] %s', message);
	},

	handleError: function(message, script, line) {
		if (!message.length || !script.length) {
			return false;
		}

		DpErrorLog.logError(message + ' (' + script + ' on line ' + line + ')', '', script, line);

		if (this._origHandler) {
			var args = Array.prototype.slice.call(arguments);
			this._origHandler.apply(this._origHandler, args);
		}

		return true;
	}
};

if (DP_DEBUG && DP_DEBUG_EVENT_TIMER) {
	var oldTrigger = jQuery.event.trigger;
	jQuery.event.trigger = function() {
		var begin = new Date();

		var args = Array.prototype.slice.call(arguments);
		oldTrigger.apply(jQuery.event, args);

		var time = (new Date()).getTime() - begin.getTime();
		if (time > 150) {
			DpErrorLog.logError("Event took "+time+"ms", printStackTrace().join("\n"));
		}
	}
}