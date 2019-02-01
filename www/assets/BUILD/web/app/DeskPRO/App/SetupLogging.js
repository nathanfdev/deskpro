define([
  'DeskPRO/Logger/Logger',
  'DeskPRO/Logger/Handler/ConsoleHandler',
  'DeskPRO/Logger/Logging/InterfaceTimer',
], (
  Logger,
  Logger_ConsoleHandler,
  DeskPRO_Logging_InterfaceTimer,
) =>
  function(Module) {

    Module.factory('LoggerManager', [ function() {
      class LoggerManager {
        constructor() {
          this.loggers = {};
        }

        get(id) {
          if (this.loggers[id]) {
            return this.loggers[id];
          }

          this.loggers[id] = this._makeLogger(id);
          return this.loggers[id];
        }

        _makeLogger(id) {
          const logger = new Logger(id);
          const consoleHandler = new Logger_ConsoleHandler(Logger.DEBUG);
          logger.pushHandler(consoleHandler);
          return logger;
        }
      }

      const lm = new LoggerManager();
      return lm;
    }
    ]);

    Module.factory('$exceptionHandler', [ () =>
      function(exception, cause) {
        if (window.trackJs) {
          return window.trackJs.track(exception);
        } else {
          if (exception.stack) {
            console.error(exception.stack);
          } else if (exception.message) {
            console.error(exception.message);
          }

          throw exception;
        }
      }
    
    ]);

    Module.factory('dpInterfaceTimer', [ '$log', $log => new DeskPRO_Logging_InterfaceTimer($log)
    ]);

    return Module.config(['$provide', $provide =>
      $provide.decorator('$log', ['LoggerManager', '$delegate', function(LoggerManager, $delegate) {
        const logger = LoggerManager.get('main');
        return logger;
      }
      ])
    
    ]);
  }
);