(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Util', 'DeskPRO/Logger/Handler/AbstractProcessingHandler'], function(Util, AbstractProcessingHandler) {
    var ConsoleHandler, _ref;
    return ConsoleHandler = (function(_super) {
      __extends(ConsoleHandler, _super);

      function ConsoleHandler() {
        _ref = ConsoleHandler.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      ConsoleHandler.prototype.write = function(record) {
        var consoleName, messageExtra, prefix, _ref1, _ref2;
        consoleName = null;
        if (record.level_name === 'debug') {
          consoleName = 'debug';
        } else if (record.level_name === 'info') {
          consoleName = 'info';
        } else if ((_ref1 = record.level_name) === 'error' || _ref1 === 'critical' || _ref1 === 'alert' || _ref1 === 'emergency') {
          consoleName = 'error';
        } else {
          consoleName = 'log';
        }
        if (((_ref2 = window.console) != null ? _ref2[consoleName] : void 0) != null) {
          prefix = "[" + record.channel + "." + record.level_name + "] ";
          messageExtra = '';
          if (record.extra.error) {
            messageExtra = this._formatError(record.extra.error);
          }
          if ((record.messageRaw != null) && Util.isArray(record.messageRaw)) {
            record.messageRaw[0] = prefix + record.messageRaw[0] + messageExtra;
            return window.console[consoleName].apply(window.console, record.messageRaw);
          } else {
            return window.console[consoleName].apply(window.console, [prefix + record.message + messageExtra]);
          }
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

      return ConsoleHandler;

    })(AbstractProcessingHandler);
  });

}).call(this);

/*
//@ sourceMappingURL=ConsoleHandler.js.map
*/