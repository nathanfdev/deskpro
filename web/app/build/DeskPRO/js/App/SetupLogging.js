(function() {
  define(['DeskPRO/Logger/Logger', 'DeskPRO/Logger/Handler/ConsoleHandler', 'DeskPRO/Logger/Logging/InterfaceTimer'], function(Logger, Logger_ConsoleHandler, DeskPRO_Logging_InterfaceTimer) {
    return function(Module) {
      Module.factory('LoggerManager', [
        function() {
          var LoggerManager, lm;
          LoggerManager = (function() {
            function LoggerManager() {
              this.loggers = {};
            }

            LoggerManager.prototype.get = function(id) {
              if (this.loggers[id]) {
                return this.loggers[id];
              }
              this.loggers[id] = this._makeLogger(id);
              return this.loggers[id];
            };

            LoggerManager.prototype._makeLogger = function(id) {
              var consoleHandler, logger;
              logger = new Logger(id);
              consoleHandler = new Logger_ConsoleHandler(Logger.DEBUG);
              logger.pushHandler(consoleHandler);
              return logger;
            };

            return LoggerManager;

          })();
          lm = new LoggerManager();
          return lm;
        }
      ]);
      Module.factory('$exceptionHandler', [
        function() {
          return function(exception, cause) {
            if (window.trackJs) {
              return window.trackJs.track(exception);
            } else {
              throw exception;
            }
          };
        }
      ]);
      Module.factory('dpInterfaceTimer', [
        '$log', function($log) {
          return new DeskPRO_Logging_InterfaceTimer($log);
        }
      ]);
      return Module.config([
        '$provide', function($provide) {
          return $provide.decorator('$log', [
            'LoggerManager', '$delegate', function(LoggerManager, $delegate) {
              var logger;
              logger = LoggerManager.get('main');
              return logger;
            }
          ]);
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupLogging.js.map
