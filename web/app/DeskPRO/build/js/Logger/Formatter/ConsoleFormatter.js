(function() {
  define(['DeskPRO/Util/Strings'], function(Strings) {
    var ConsoleFormatter;
    return ConsoleFormatter = (function() {
      function ConsoleFormatter(formatString) {
        this.formatString = formatString;
        if (!this.formatString) {
          this.formatString = ConsoleFormatter.SIMPLE_FORMAT;
        }
      }

      ConsoleFormatter.prototype.format = function(record) {
        var console_args, console_format, format_string, k, v, _ref;
        format_string = this.formatString;
        if (record.messageConsole) {
          console_args = record.messageConsole;
          console_format = console_args.shift();
        } else {
          console_args = [];
          console_format = record.message;
        }
        _ref = record.extra;
        for (k in _ref) {
          v = _ref[k];
          format_string = format_string.replace(new RegExp(Strings.escapeRegex("%extra." + k + "%"), 'g'), v + "");
        }
        for (k in record) {
          v = record[k];
          format_string = format_string.replace(new RegExp(Strings.escapeRegex("%" + k + "%"), 'g'), v + "");
        }
        format_string = format_string.replace(/%console_format%/g, console_format);
        return {
          format: format_string,
          args: console_args
        };
      };

      ConsoleFormatter.SIMPLE_FORMAT = "[%dateStr%] (%channel%.%level_name%) %console_format%";

      return ConsoleFormatter;

    })();
  });

}).call(this);

//# sourceMappingURL=ConsoleFormatter.js.map
