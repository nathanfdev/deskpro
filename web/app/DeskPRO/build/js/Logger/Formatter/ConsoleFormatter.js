(function() {
  define(['DeskPRO/Util/Strings'], function(Strings) {
    var LineFormatter;
    return LineFormatter = (function() {
      function LineFormatter(formatString) {
        this.formatString = formatString;
        if (!this.formatString) {
          this.formatString = LineFormatter.SIMPLE_FORMAT;
        }
      }

      LineFormatter.prototype.format = function(record) {
        var console_args, console_format, format_string, k, v, _ref;
        format_string = this.formatString;
        console_args = record.messageConsole;
        console_format = console_args.shift();
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

      LineFormatter.SIMPLE_FORMAT = "[%date%] %channel%.%level_name%: %console_format%";

      return LineFormatter;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=ConsoleFormatter.js.map
*/