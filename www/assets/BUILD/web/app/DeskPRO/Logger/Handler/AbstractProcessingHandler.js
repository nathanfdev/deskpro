define([
  'DeskPRO/Util/Util',
  'DeskPRO/Logger/Handler/AbstractHandler',
], function(
  Util,
  AbstractHandler
) {
  class AbstractProcessingHandler extends AbstractHandler {
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
  }
  return AbstractProcessingHandler;
});