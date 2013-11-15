define [
	'DeskPRO/Util/Strings'
], (
	Strings
) ->
	class LineFormatter
		constructor: (@format) ->
			if not @format
				@format = LineFormatter.SIMPLE_FORMAT


		format: (record) ->
			output = @format

			for k, v of record.extra
				output = output.replace(new RegExp(Strings.escapeRegex("%extra.#{k}%"), 'g'), v + "")
			for k, v of record
				output = output.replace(new RegExp(Strings.escapeRegex("%#{k}%"), 'g'), v + "")

			return output

		@SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"