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
        var k, output, v, _ref;
        output = this.formatString;
        _ref = record.extra;
        for (k in _ref) {
          v = _ref[k];
          output = output.replace(new RegExp(Strings.escapeRegex("%extra." + k + "%"), 'g'), v + "");
        }
        for (k in record) {
          v = record[k];
          output = output.replace(new RegExp(Strings.escapeRegex("%" + k + "%"), 'g'), v + "");
        }
        return output;
      };

      LineFormatter.SIMPLE_FORMAT = "[%dateStr%] %channel%.%level_name%: %message% %context% %extra%\n";

      return LineFormatter;

    })();
  });

}).call(this);

//# sourceMappingURL=LineFormatter.js.map
