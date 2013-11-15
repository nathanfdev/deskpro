define [
	'DeskPRO/Util/Strings'
], (
	Strings
) ->
	class LineFormatter
		constructor: (@formatString) ->
			if not @formatString
				@formatString = LineFormatter.SIMPLE_FORMAT


		format: (record) ->
			format_string = @formatString
			console_args = record.messageConsole
			console_format = console_args.shift()

			for k, v of record.extra
				format_string = format_string.replace(new RegExp(Strings.escapeRegex("%extra.#{k}%"), 'g'), v + "")
			for k, v of record
				format_string = format_string.replace(new RegExp(Strings.escapeRegex("%#{k}%"), 'g'), v + "")

			format_string = format_string.replace(/%console_format%/g, console_format)

			return {
				format: format_string,
				args: console_args
			}

		@SIMPLE_FORMAT = "[%date%] %channel%.%level_name%: %console_format%"