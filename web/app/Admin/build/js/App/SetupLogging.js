(function() {
  define(['DeskPRO/Logger/Logger', 'DeskPRO/Logger/Handler/ConsoleHandler', 'Admin/Logging/InterfaceTimer'], function(Logger, Logger_ConsoleHandler, Admin_Logging_InterfaceTimer) {
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
      Module.factory('jsErrorLogger', [
        '$injector', function($injector) {
          var jsErrorLogger;
          jsErrorLogger = (function() {
            function jsErrorLogger() {}

            jsErrorLogger.prototype.getApi = function() {
              return $injector.get('Api');
            };

            jsErrorLogger.prototype.logScriptError = function(message, scriptFile, scriptLine, trace, context_data) {
              if (scriptFile == null) {
                scriptFile = '';
              }
              if (scriptLine == null) {
                scriptLine = 0;
              }
              if (trace == null) {
                trace = '';
              }
              if (context_data == null) {
                context_data = {};
              }
              if (context_data.url == null) {
                context_data.url = window.location + '';
              }
              try {
                return this.getApi().sendPostJson('/log-js-error', {
                  message: message,
                  script_file: scriptFile,
                  script_line: scriptLine,
                  trace: trace,
                  context: context_data
                });
              } catch (_error) {}
            };

            jsErrorLogger.prototype.logException = function(exception, context_data) {
              var trace;
              trace = printStackTrace({
                e: exception
              });
              if (trace) {
                trace = trace.join("\n");
              }
              if (exception instanceof Error || (exception.message != null)) {
                return this.logScriptError(exception.message, exception.fileName || '', exception.lineNumber || '', trace, context_data);
              } else if (exception.sourceURL != null) {
                return this.logScriptError(exception.message, exception.sourceURL, exception.line, trace);
              }
            };

            jsErrorLogger.prototype.logErrorMessage = function(message, context_data) {
              return this.logError(message);
            };

            return jsErrorLogger;

          })();
          return new jsErrorLogger();
        }
      ]);
      Module.factory('$exceptionHandler', [
        'jsErrorLogger', function(jsErrorLogger) {
          return function(exception, cause) {
            return window.setTimeout(function() {
              jsErrorLogger.logException(exception);
              exception._dpNoLog = true;
              throw exception;
            }, 1);
          };
        }
      ]);
      Module.factory('dpInterfaceTimer', [
        '$log', function($log) {
          return new Admin_Logging_InterfaceTimer($log);
        }
      ]);
      Module.config([
        '$provide', function($provide) {
          return $provide.decorator('$rootScope', [
            'dpInterfaceTimer', '$delegate', function(dpInterfaceTimer, $delegate) {
              var origDigest;
              origDigest = $delegate.$digest;
              $delegate.$digest = function() {
                var ret;
                dpInterfaceTimer.startDigest();
                ret = origDigest.apply($delegate, arguments);
                dpInterfaceTimer.endDigest();
                return ret;
              };
              return $delegate;
            }
          ]);
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

/*
//@ sourceMappingURL=SetupLogging.js.map
*/