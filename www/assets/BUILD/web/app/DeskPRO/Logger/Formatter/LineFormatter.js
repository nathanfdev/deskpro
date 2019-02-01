// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Strings'
], function(
  Strings
) {
  class LineFormatter {
    static initClass() {

      this.SIMPLE_FORMAT = "[%dateStr%] %channel%.%level_name%: %message% %context% %extra%\n";
    }
    constructor(formatString) {
      this.formatString = formatString;
      if (!this.formatString) {
        this.formatString = LineFormatter.SIMPLE_FORMAT;
      }
    }


    format(record) {
      let v;
      let output = this.formatString;

      for (var k in record.extra) {
        v = record.extra[k];
        output = output.replace(new RegExp(Strings.escapeRegex(`%extra.${k}%`), 'g'), v + "");
      }
      for (k in record) {
        v = record[k];
        output = output.replace(new RegExp(Strings.escapeRegex(`%${k}%`), 'g'), v + "");
      }

      return output;
    }
  }
  LineFormatter.initClass();
  return LineFormatter;
});