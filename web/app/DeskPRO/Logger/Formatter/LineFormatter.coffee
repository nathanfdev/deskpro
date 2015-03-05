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
      output = @formatString

      for k, v of record.extra
        output = output.replace(new RegExp(Strings.escapeRegex("%extra.#{k}%"), 'g'), v + "")
      for k, v of record
        output = output.replace(new RegExp(Strings.escapeRegex("%#{k}%"), 'g'), v + "")

      return output

    @SIMPLE_FORMAT = "[%dateStr%] %channel%.%level_name%: %message% %context% %extra%\n"