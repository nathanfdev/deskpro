/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Strings'
], function(
  Util,
  Strings
){
  let Logger;
  return Logger = (function() {
    Logger = class Logger {
      static initClass() {
  
  
        this.DEBUG     = 100;
        this.INFO      = 200;
        this.NOTICE    = 250;
        this.WARNING   = 300;
        this.ERROR     = 400;
        this.CRITICAL  = 500;
        this.ALERT     = 550;
        this.EMERGENCY = 600;
        this.LEVELS    = {
          100: 'DEBUG',
          200: 'INFO',
          250: 'NOTICE',
          300: 'WARNING',
          400: 'ERROR',
          500: 'CRITICAL',
          550: 'ALERT',
          600: 'EMERGENCY',
        };
      }
      /*
        * @param {String} name
      */
      constructor(name) {
        this.name = name;
        this.handlers = [];
        this.processors = [];
      }


      /*
        * @return {String}
      */
      getName() {
        return this.name;
      }


      /*
        * @param {Object} handler
      */
      pushHandler(handler) {
        return this.handlers.unshift(handler);
      }


      /*
        * @return {Object}
      */
      popHandler() {
        return this.handlers.shift();
      }


      /*
        * @return {Function}
      */
      pushProcessor(processor) {
        return this.processors.unshift(processor);
      }


      /*
        * @return {Object}
      */
      popProcessor() {
        return this.processors.shift();
      }


      /*
        * @param {Integer} level
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      addRecord(level, message, context) {
        let messageConsole, messageString;
        if (context == null) { context = {}; }
        if (!this.handlers.length) {
          return false;
        }

        const messageRaw = message;

        if (Util.isArray(message)) {
          const messageFormat = message.shift();
          const stringArgs = [];
          const consoleArgs = [];

          for (let v of Array.from(message)) {
            if (Util.isString(v) || Util.isNumber(v)) {
              stringArgs.push(v + "");
              consoleArgs.push("%s");
            } else {
              stringArgs.push(Util.dump(v));
              consoleArgs.push("%o");
            }
          }

          messageString = Strings.format(messageFormat, stringArgs);
          messageConsole = Util.clone(message);
          messageConsole.unshift(Strings.format(messageFormat, consoleArgs));
        } else if (Util.isString(message) || Util.isNumber(message)) {
          messageString = message;
          messageConsole = [message];
        } else {
          messageString  = Util.dump(message);
          messageConsole = ["{0}", message];
        }

        const date = new Date();

        const dateStr = date.getUTCFullYear() + '-' +
          Strings.prePad(date.getUTCMonth()+1, '0', 2) + '-' +
          Strings.prePad(date.getUTCDate(), '0', 2) + ' ' +
          Strings.prePad(date.getUTCHours(), '0', 2) + ':' +
          Strings.prePad(date.getUTCMinutes(), '0', 2) + ':' +
          Strings.prePad(date.getUTCSeconds(), '0', 2) + '.' +
          Strings.prePad(date.getUTCMilliseconds(), '0', 3);

        let record = {
          message:        messageString,
          messageRaw,
          messageConsole,
          context,
          level,
          level_name:     Logger.LEVELS[level],
          channel:        this.name,
          date,
          dateStr,
          extra:          {}
        };

        let handlerKey = null;
        for (let k = 0; k < this.handlers.length; k++) {
          const handler = this.handlers[k];
          if (handler.isHandling(record)) {
            handlerKey = k;
            break;
          }
        }

        if (handlerKey === null) {
          return false;
        }

        for (let proc of Array.from(this.processors)) {
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
      }


      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      debug(message, context) {
        return this.addRecord(Logger.DEBUG, message, context);
      }


      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      info(message, context) {
        return this.addRecord(Logger.INFO, message, context);
      }

      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      notice(message, context) {
        return this.addRecord(Logger.NOTICE, message, context);
      }

      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      warning(message, context) {
        return this.addRecord(Logger.WARNING, message, context);
      }


      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      error(message, context) {
        return this.addRecord(Logger.ERROR, message, context);
      }


      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      critical(message, context) {
        return this.addRecord(Logger.CRITICAL, message, context);
      }


      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      alert(message, context) {
        return this.addRecord(Logger.ALERT, message, context);
      }


      /*
        * @param {String} message
        * @param {Object} context
        * @return {bool}
      */
      emergency(message, context) {
        return this.addRecord(Logger.EMERGENCY, message, context);
      }
    };
    Logger.initClass();
    return Logger;
  })();
});