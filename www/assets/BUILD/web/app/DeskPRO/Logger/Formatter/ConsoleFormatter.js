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
  let ConsoleFormatter;
  return ConsoleFormatter = (function() {
    ConsoleFormatter = class ConsoleFormatter {
      static initClass() {
  
        this.SIMPLE_FORMAT = "[%dateStr%] (%channel%.%level_name%) %console_format%";
      }
      constructor(formatString) {
        this.formatString = formatString;
        if (!this.formatString) {
          this.formatString = ConsoleFormatter.SIMPLE_FORMAT;
        }
      }


      format(record) {
        let console_args, console_format, v;
        let format_string = this.formatString;
        if (record.messageConsole) {
          console_args = record.messageConsole;
          console_format = console_args.shift();
        } else {
          console_args = [];
          console_format = record.message;
        }

        for (var k in record.extra) {
          v = record.extra[k];
          format_string = format_string.replace(new RegExp(Strings.escapeRegex(`%extra.${k}%`), 'g'), v + "");
        }
        for (k in record) {
          v = record[k];
          format_string = format_string.replace(new RegExp(Strings.escapeRegex(`%${k}%`), 'g'), v + "");
        }

        format_string = format_string.replace(/%console_format%/g, console_format);

        return {
          format: format_string,
          args: console_args
        };
      }
    };
    ConsoleFormatter.initClass();
    return ConsoleFormatter;
  })();
});