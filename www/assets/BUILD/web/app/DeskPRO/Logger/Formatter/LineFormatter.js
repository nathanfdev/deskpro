define([
  'DeskPRO/Util/Strings'
], (
  Strings
) => {
  class LineFormatter {
    static initClass() {
      this.SIMPLE_FORMAT = '[%dateStr%] %channel%.%level_name%: %message% %context% %extra%\n';
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
        output = output.replace(new RegExp(Strings.escapeRegex(`%extra.${k}%`), 'g'), `${v}`);
      }
      for (k in record) {
        v = record[k];
        output = output.replace(new RegExp(Strings.escapeRegex(`%${k}%`), 'g'), `${v}`);
      }

      return output;
    }
  }
  LineFormatter.initClass();
  return LineFormatter;
});
