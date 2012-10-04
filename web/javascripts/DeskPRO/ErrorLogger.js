var DpErrorLog = {
	saveUrl: null,
	hasSentReport: false,
	logCount: 0,
	init: function() {
		if (!this.saveUrl) {
			return;
		}

		var self = this;

		var gOldOnError = window.onerror;

		window.onerror = function(message, script, line) {
			self.handleError(message, script, line);

			if (gOldOnError) {
				gOldOnError(message, script, line);
			}

			if (!DP_DEBUG) {
				if ($.browser.mozilla) {
					// https://developer.mozilla.org/en-US/docs/DOM/window.onerror
					// true to say error was handled
					return true;
				} else {
					// (standard) http://code.google.com/p/chromium/issues/detail?id=92062
					// false to say error was handled
					return false;
				}
			}
		};

		if (window.jQuery && window.jQuery.cookie) {
			if ($.cookie('dp_jse_report')) {
				this.hasSentReport = true;
			}
		}
	},

	logError: function(message, trace, script, line) {

		if (window.DP_LOADED_TIME) {
			var timeUsing = ((new Date()).getTime() / 1000) - window.DP_LOADED_TIME;
		} else {
			var timeUsing = 0;
		}

		if (window.console.log) {
			window.console.log('[JS Error] %s (%s %d): %s', message, script, line, trace);
		}

		if (!message || message == 'false' || (message == 'Script error.' && line == '0')) {
			return;
		}

		if (parseInt(line) == 1 && script.indexOf('/agent/') != -1) {
			return;
		}

		// Send max 5 per session
		if (this.logCount++ > 5) {
			return;
		}

		message += ' (timeUsing: ' + timeUsing + ')';

		var data = {
			message: message || '',
			trace:   trace   || '',
			script:  script  || '',
			line:    line    || '0'
		};

		if (this.saveUrl) {

			message = message+'';

			if (ASSETS_BASE_URL) {
				var r = new RegExp(ASSETS_BASE_URL.escapeRegExp(), 'g');
				message = message.replace(r, '');
			}

			if (data.script.indexOf('#app.') !== -1) {
				data.script = data.script.replace(/#.*$/, '');
			}

			$.ajax({
				url: this.saveUrl,
				data: data,
				error: function() { },// prevents DeskPRO_Window's global error handler from firing on error
				type: 'POST'
			});
		}

		if (window.SEND_FEEDBACK_WINDOW && !this.hasSentReport) {
			this.hasSentReport = true;

			if (window.jQuery && window.jQuery.cookie) {
				$.cookie('dp_jse_report', '1', { expires: 1 });
			}

			window.SEND_FEEDBACK_WINDOW.open(
				"We have detected a browser Javascript error that may prevent the interface from functioning properly. " +
				"To help us identify and fix the problem, we would appreciate it if you could describe what you were viewing " +
				"and the actions you were performing just before this notice appeared.",

				"Message: " + data.message + "\nScript: " + data.script + "\nLine:" + data.line + "\nUser Agent: " + navigator.userAgent,

				true
			);
		}
	},

	handleError: function(message, script, line) {
		if (!message || !message.length || !script.length) {
			return false;
		}

		DpErrorLog.logError(message + ' (' + script + ' on line ' + line + ')', '', script, line);

		return true;
	}
};

if (typeof DP_DEBUG != 'undefined' && typeof DP_DEBUG_EVENT_TIMER != 'undefined' && DP_DEBUG && DP_DEBUG_EVENT_TIMER) {
	var oldTrigger = jQuery.event.trigger;
	jQuery.event.trigger = function() {
		var begin = new Date();

		var args = Array.prototype.slice.call(arguments);
		oldTrigger.apply(jQuery.event, args);

		var time = (new Date()).getTime() - begin.getTime();
		if (time > 150) {
			DpErrorLog.logError("Event took "+time+"ms");
		}
	}
}