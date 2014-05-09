(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Util', 'DeskPRO/Logger/Handler/AbstractProcessingHandler', 'DeskPRO/Logger/Formatter/ConsoleFormatter'], function(Util, AbstractProcessingHandler, ConsoleFormatter) {
    var ConsoleHandler;
    return ConsoleHandler = (function(_super) {
      __extends(ConsoleHandler, _super);

      function ConsoleHandler() {
        return ConsoleHandler.__super__.constructor.apply(this, arguments);
      }

      ConsoleHandler.prototype.write = function(record) {
        var consoleName, format_args, _ref, _ref1;
        consoleName = null;
        if (record.level_name === 'debug') {
          consoleName = 'debug';
        } else if (record.level_name === 'info') {
          consoleName = 'info';
        } else if ((_ref = record.level_name) === 'error' || _ref === 'critical' || _ref === 'alert' || _ref === 'emergency') {
          consoleName = 'error';
        } else {
          consoleName = 'log';
        }
        if (((_ref1 = window.console) != null ? _ref1[consoleName] : void 0) != null) {
          format_args = record.formatted.args;
          format_args.unshift(record.formatted.format);
          return window.console[consoleName].apply(window.console, format_args);
        }
      };

      ConsoleHandler.prototype._formatError = function() {
        var arg;
        if (arg instanceof Error) {
          if (arg.stack) {
            if (arg.message && arg.stack.indexOf(arg.message) === -1) {
              arg = 'Error: ' + arg.message + '\n' + arg.stack;
            } else {
              arg = arg.stack;
            }
          } else if (arg.sourceURL) {
            arg = arg.message + '\n' + arg.sourceURL + ':' + arg.line;
          }
        }
        return arg;
      };


      /*
        	 * @return {Object}
       */

      ConsoleHandler.prototype.getDefaultFormatter = function() {
        return new ConsoleFormatter();
      };

      return ConsoleHandler;

    })(AbstractProcessingHandler);
  });

}).call(this);

//# sourceMappingURL=ConsoleHandler.js.map
