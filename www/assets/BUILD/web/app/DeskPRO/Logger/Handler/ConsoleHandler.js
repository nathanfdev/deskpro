define([
  'DeskPRO/Util/Util',
  'DeskPRO/Logger/Handler/AbstractProcessingHandler',
  'DeskPRO/Logger/Formatter/ConsoleFormatter',
], function(
  Util,
  AbstractProcessingHandler,
  ConsoleFormatter
) {
  class ConsoleHandler extends AbstractProcessingHandler {
    write(record) {
      let consoleName = null;
      if (record.level_name === 'debug') {
        consoleName = 'debug';
      } else if (record.level_name === 'info') {
        consoleName = 'info';
      } else if (['error', 'critical', 'alert', 'emergency'].includes(record.level_name)) {
        consoleName = 'error';
      } else {
        consoleName = 'log';
      }

      if ((window.console != null ? window.console[consoleName] : undefined) != null) {
        const format_args = record.formatted.args;
        format_args.unshift(record.formatted.format);

        return window.console[consoleName].apply(window.console, format_args);
      }
    }

    _formatError() {
      let arg;
      if (arg instanceof Error) {
        if (arg.stack) {
          if (arg.message && (arg.stack.indexOf(arg.message) === -1)) {
            arg = `Error: ${arg.message}\n${arg.stack}`;
          } else {
            arg = arg.stack;
          }
        } else if (arg.sourceURL) {
          arg = arg.message + '\n' + arg.sourceURL + ':' + arg.line;
        }
      }

      return arg;
    }

    /*
      * @return {Object}
    */
    getDefaultFormatter() {
      return new ConsoleFormatter();
    }
  }
  return ConsoleHandler;
});