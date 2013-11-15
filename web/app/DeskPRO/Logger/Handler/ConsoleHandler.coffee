define [
	'DeskPRO/Util/Util'
	'DeskPRO/Logger/Handler/AbstractProcessingHandler',
	'DeskPRO/Logger/Formatter/ConsoleFormatter',
], (
	Util,
	AbstractProcessingHandler,
	ConsoleFormatter
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
				format_args = record.formatted.args
				format_args.unshift(record.formatted.format)

				window.console[consoleName].apply(window.console, format_args)

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

		###
    	# @return {Object}
		###
		getDefaultFormatter: ->
			return new ConsoleFormatter()