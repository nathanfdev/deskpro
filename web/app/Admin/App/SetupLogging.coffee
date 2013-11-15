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
			$provide.decorator('$rootScope', ['dpInterfaceTimer', '$delegate', (dpInterfaceTimer, $delegate) ->
				origDigest = $delegate.$digest
				$delegate.$digest = ->
					dpInterfaceTimer.startDigest()
					ret = origDigest.apply($delegate, arguments)
					dpInterfaceTimer.endDigest()
					return ret

				return $delegate
			])
		])

		Module.config(['$provide', ($provide) ->
			$provide.decorator('$log', ['$delegate', ($delegate) ->
				logger = new Logger('console')
				consoleHandler = new Logger_ConsoleHandler(Logger.DEBUG)
				logger.pushHandler(consoleHandler)

				return logger
			])
		])