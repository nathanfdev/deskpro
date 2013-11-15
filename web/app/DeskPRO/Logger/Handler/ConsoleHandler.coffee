define [
	'DeskPRO/Util/Util'
	'DeskPRO/Logger/Handler/AbstractProcessingHandler',
], (
	Util,
	AbstractProcessingHandler
) ->
	class ConsoleHandler extends AbstractProcessingHandler
		write: (record) ->
			consoleName = null
			if record.level_name == 'debug'
				consoleName = 'debug'
			else if record.level_name == 'info'
				consoleName = 'info'
			else if record.level_name in ['error', 'critical', 'alert', 'emergency']
				consoleName = 'error'
			else
				consoleName = 'log'

			if window.console?[consoleName]?
				prefix = "[#{record.channel}.#{record.level_name}] "
				messageExtra = ''
				if record.extra.error
					messageExtra = @_formatError(record.extra.error)

				if record.messageRaw? and Util.isArray(record.messageRaw)
					record.messageRaw[0] = prefix + record.messageRaw[0] + messageExtra
					window.console[consoleName].apply(window.console, record.messageRaw)
				else
					window.console[consoleName].apply(window.console, [prefix + record.message + messageExtra])

		_formatError: ->
			if arg instanceof Error
				if arg.stack
					if arg.message and arg.stack.indexOf(arg.message) == -1
						arg = 'Error: ' + arg.message + '\n' + arg.stack
					else
						arg = arg.stack
				else if arg.sourceURL
					arg = arg.message + '\n' + arg.sourceURL + ':' + arg.line

			return arg