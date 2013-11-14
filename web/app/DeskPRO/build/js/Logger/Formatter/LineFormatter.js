(function() {
  define(['DeskPRO/Util/Strings'], function(Strings) {
    var LineFormatter;
    return LineFormatter = (function() {
      function LineFormatter(format) {
        this.format = format;
        if (!this.format) {
          this.format = LineFormatter.SIMPLE_FORMAT;
        }
      }

      LineFormatter.prototype.format = function(record) {
        var k, output, v, _ref;
        output = this.format;
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

      LineFormatter.SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

      return LineFormatter;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=LineFormatter.js.map
*/