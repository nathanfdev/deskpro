define [
  'DeskPRO/Logger/Logger',
  'DeskPRO/Logger/Handler/ConsoleHandler',
  'DeskPRO/Logger/Logging/InterfaceTimer',
], (
  Logger,
  Logger_ConsoleHandler,
  DeskPRO_Logging_InterfaceTimer,
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

    Module.factory('$exceptionHandler', [ ->
      return (exception, cause) ->
        if window.trackJs
          window.trackJs.track(exception)
        else
          if exception.stack
            console.error(exception.stack)
          else if exception.message
            console.error(exception.message)

          throw exception
    ])

    Module.factory('dpInterfaceTimer', [ '$log', ($log) ->
      return new DeskPRO_Logging_InterfaceTimer($log)
    ])

    Module.config(['$provide', ($provide) ->
      $provide.decorator('$log', ['LoggerManager', '$delegate', (LoggerManager, $delegate) ->
        logger = LoggerManager.get('main')
        return logger
      ])
    ])