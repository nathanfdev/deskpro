// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Logger/Logger',
  'DeskPRO/Logger/Formatter/LineFormatter',
], function(
  Logger,
  LineFormatter
) {
  let AbstractHandler;
  return (AbstractHandler = class AbstractHandler {
    constructor(level, bubble) {
      if (level == null) { level = Logger.DEBUG; }
      this.level = level;
      if (bubble == null) { bubble = true; }
      this.bubble = bubble;
      this.processors = [];
      this.formatter = null;
    }


    /*
      * @param {Object} record
      * @return {bool}
      */
    isHandling(record) {
      return record.level >= this.level;
    }


    /*
      * Handle a number of records at once
      *
      * @param {Array} records
      */
    handleBatch(records) {
      return Array.from(records).map((rec) =>
        this.handle(rec));
    }


    /*
      * Handle the log record
      *
      * @param {Object} record
      * @return {bool}
      */
    handle(record) {
      throw new Error("Unimplemented");
    }


    /*
      * @return {Function}
    */
    pushProcessor(processor) {
      this.processors.unshift(processor);
      return this;
    }


    /*
      * @return {Object}
    */
    popProcessor() {
      return this.processors.shift();
    }

    /*
      * Sets the formatter
      *
      * @param {Object} formatter
    */
    setFormatter(formatter) {
      this.formatter = formatter;
      return this;
    }


    /*
      * @return {Object}
    */
    getFormatter() {
      if (this.formatter === null) {
        this.formatter = this.getDefaultFormatter();
      }

      return this.formatter;
    }


    /*
      * @return {Object}
    */
    getDefaultFormatter() {
      return new LineFormatter();
    }


    /*
      * @return {Integer}
    */
    getLevel() {
      return this.level;
    }

    /*
      * Sets the level
      *
      * @param {Integer} level
    */
    setLevel(level) {
      this.level = level;
      return this;
    }


    /*
      * @return {bool}
    */
    getBubble() {
      return this.bubble;
    }


    /*
      * Enable/disable bubble
      *
      * @param {bool} bubble
    */
    setBubble(bubble) {
      this.bubble = bubble;
      return this;
    }
  });
});