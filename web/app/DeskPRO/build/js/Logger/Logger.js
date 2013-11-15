(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Logger;
    return Logger = (function() {
      /*
        	# @param {String} name
      */

      function Logger(name) {
        this.name = name;
        this.handlers = [];
        this.processors = [];
      }

      /*
        	# @return {String}
      */


      Logger.prototype.getName = function() {
        return this.name;
      };

      /*
        	# @param {Object} handler
      */


      Logger.prototype.pushHandler = function(handler) {
        return this.handlers.unshift(handler);
      };

      /*
        	# @return {Object}
      */


      Logger.prototype.popHandler = function() {
        return this.handlers.shift();
      };

      /*
        	# @return {Function}
      */


      Logger.prototype.pushProcessor = function(processor) {
        return this.processors.unshift(processor);
      };

      /*
        	# @return {Object}
      */


      Logger.prototype.popProcessor = function() {
        return this.processors.shift();
      };

      /*
        	# @param {Integer} level
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.addRecord = function(level, message, context) {
        var handler, handlerKey, k, messageRaw, proc, record, _i, _j, _len, _len1, _ref, _ref1;
        if (context == null) {
          context = {};
        }
        if (!this.handlers.length) {
          return false;
        }
        messageRaw = message;
        if (Util.isArray(message)) {
          message = message.join(" ");
        }
        record = {
          message: message,
          messageRaw: messageRaw,
          context: context,
          level: level,
          level_name: Logger.LEVELS[level],
          channel: this.name,
          date: new Date(),
          extra: {}
        };
        handlerKey = null;
        _ref = this.handlers;
        for (k = _i = 0, _len = _ref.length; _i < _len; k = ++_i) {
          handler = _ref[k];
          if (handler.isHandling(record)) {
            handlerKey = k;
            break;
          }
        }
        if (handlerKey === null) {
          return false;
        }
        _ref1 = this.processors;
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
          proc = _ref1[_j];
          if (proc.process != null) {
            record = proc.process(record);
          } else {
            record = proc(record);
          }
        }
        while (this.handlers[handlerKey] != null) {
          if (this.handlers[handlerKey].isHandling(record)) {
            if (this.handlers[handlerKey].handle(record)) {
              break;
            }
          }
          handlerKey++;
        }
        return true;
      };

      /*
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.debug = function(message, context) {
        return this.addRecord(Logger.DEBUG, message, context);
      };

      /*
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.info = function(message, context) {
        return this.addRecord(Logger.INFO, message, context);
      };

      /*
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.warning = function(message, context) {
        return this.addRecord(Logger.WARN, message, context);
      };

      /*
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.error = function(message, context) {
        return this.addRecord(Logger.ERROR, message, context);
      };

      /*
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.critical = function(message, context) {
        return this.addRecord(Logger.CRITICAL, message, context);
      };

      /*
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.alert = function(message, context) {
        return this.addRecord(Logger.ALERT, message, context);
      };

      /*
        	# @param {String} message
        	# @param {Object} context
        	# @return {bool}
      */


      Logger.prototype.emergency = function(message, context) {
        return this.addRecord(Logger.EMERGENCY, message, context);
      };

      Logger.DEBUG = 100;

      Logger.INFO = 200;

      Logger.NOTICE = 250;

      Logger.WARNING = 300;

      Logger.ERROR = 400;

      Logger.CRITICAL = 500;

      Logger.ALERT = 550;

      Logger.EMERGENCY = 600;

      Logger.LEVELS = {
        100: 'DEBUG',
        200: 'INFO',
        250: 'NOTICE',
        300: 'WARNING',
        400: 'ERROR',
        500: 'CRITICAL',
        550: 'ALERT',
        600: 'EMERGENCY'
      };

      return Logger;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=Logger.js.map
*/