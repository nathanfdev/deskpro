define [
  'DeskPRO/Util/Util'
  'DeskPRO/Logger/Handler/AbstractHandler',
], (
  Util,
  AbstractHandler
) ->
  class AbstractProcessingHandler extends AbstractHandler
    handle: (record) ->
      if not @isHandling(record)
        return false

      record = Util.clone(record, false)
      record = @processRecord(record)

      formatter = @getFormatter()
      if formatter
        if formatter.format?
          record.formatted = formatter.format(record)
        else
          record.formatted = formatter(record)

      @write(record)

      return @bubble == false

    write: (record) ->
      throw new Error("Unimplemented")

    processRecord: (record) ->
      for proc in @processors
        if proc.process?
          record = proc.process(record)
        else
          record = proc(record)

      return record