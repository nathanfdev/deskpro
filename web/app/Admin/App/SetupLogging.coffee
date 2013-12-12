define [
	'DeskPRO/Logger/Logger',
	'DeskPRO/Logger/Handler/ConsoleHandler',
	'Admin/Logging/InterfaceTimer',
], (
	Logger,
	Logger_ConsoleHandler,
	Admin_Logging_InterfaceTimer,
) ->
	return (Module) ->

		Module.factory('LoggerManager', [ ->
			class LoggerManager
				constructor: ->
					@loggers = {}

				get: (id) ->
					if @loggers[id]
						return @loggers[id]

					@loggers[id] = @_makeLogger(id)
					return @loggers[id]

				_makeLogger: (id) ->
					logger = new Logger(id)
					consoleHandler = new Logger_ConsoleHandler(Logger.DEBUG)
					logger.pushHandler(consoleHandler)
					return logger

			lm = new LoggerManager()
			return lm
		])

		Module.factory('jsErrorLogger', [ '$injector', ($injector) ->
			class jsErrorLogger
				getApi: ->
					return $injector.get('Api')

				logScriptError: (message, scriptFile = '', scriptLine = 0, trace = '', context_data = {}) ->
					if not context_data.url?
						context_data.url = window.location + ''

					try
						@getApi().sendPostJson('/log-js-error', {
							message:     message,
							script_file: scriptFile,
							script_line: scriptLine,
							trace:       trace,
							context:     context_data
						})

				logException: (exception, context_data) ->
					trace = printStackTrace({e: exception})
					if trace
						trace = trace.join("\n")
					if exception instanceof Error or exception.message?
						@logScriptError(
							exception.message,
							exception.fileName || '',
							exception.lineNumber || '',
							trace,
							context_data
						)
					else if exception.sourceURL?
						@logScriptError(
							exception.message,
							exception.sourceURL,
							exception.line,
							trace
						)

				logErrorMessage: (message, context_data) ->
					@logError(message)

			return new jsErrorLogger()
		])

		Module.factory('$exceptionHandler', [ 'jsErrorLogger', (jsErrorLogger) ->
			window.DP_JS_ERROR_LOGGER = jsErrorLogger

			# only enable when not in dev/testing
			# while in dev, native browser (eg firebug etc) is better for onerrors
			if !window.DP_IS_DEBUG || window.DP_IS_TESTING
				window.onerror = (message, url, linenumber) ->
					jsErrorLogger.logScriptError(message, url, linenumber)

			return (exception, cause) ->
				window.setTimeout(->
					jsErrorLogger.logException(exception)
					exception._dpNoLog = true
					throw exception
				, 1)
		])

		Module.factory('dpInterfaceTimer', [ '$log', ($log) ->
			return new Admin_Logging_InterfaceTimer($log)
		])

		Module.config(['$provide', ($provide) ->
			$provide.decorator('$log', ['LoggerManager', '$delegate', (LoggerManager, $delegate) ->
				logger = LoggerManager.get('main')
				return logger
			])
		])