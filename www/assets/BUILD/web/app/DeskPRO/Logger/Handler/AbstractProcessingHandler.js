/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'DeskPRO/Logger/Handler/AbstractHandler',
], function(
  Util,
  AbstractHandler
) {
  let AbstractProcessingHandler;
  return (AbstractProcessingHandler = class AbstractProcessingHandler extends AbstractHandler {
    handle(record) {
      if (!this.isHandling(record)) {
        return false;
      }

      record = Util.clone(record, false);
      record = this.processRecord(record);

      const formatter = this.getFormatter();
      if (formatter) {
        if (formatter.format != null) {
          record.formatted = formatter.format(record);
        } else {
          record.formatted = formatter(record);
        }
      }

      this.write(record);

      return this.bubble === false;
    }

    write(record) {
      throw new Error("Unimplemented");
    }

    processRecord(record) {
      for (let proc of Array.from(this.processors)) {
        if (proc.process != null) {
          record = proc.process(record);
        } else {
          record = proc(record);
        }
      }

      return record;
    }
  });
});