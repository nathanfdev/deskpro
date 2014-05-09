(function() {
  define(['DeskPRO/Logger/Logger', 'DeskPRO/Logger/Formatter/LineFormatter'], function(Logger, LineFormatter) {
    var AbstractHandler;
    return AbstractHandler = (function() {
      function AbstractHandler(level, bubble) {
        this.level = level != null ? level : Logger.DEBUG;
        this.bubble = bubble != null ? bubble : true;
        this.processors = [];
        this.formatter = null;
      }


      /*
        	 * @param {Object} record
        	 * @return {bool}
       */

      AbstractHandler.prototype.isHandling = function(record) {
        return record.level >= this.level;
      };


      /*
        	 * Handle a number of records at once
        	 *
        	 * @param {Array} records
       */

      AbstractHandler.prototype.handleBatch = function(records) {
        var rec, _i, _len, _results;
        _results = [];
        for (_i = 0, _len = records.length; _i < _len; _i++) {
          rec = records[_i];
          _results.push(this.handle(rec));
        }
        return _results;
      };


      /*
        	 * Handle the log record
        	 *
        	 * @param {Object} record
        	 * @return {bool}
       */

      AbstractHandler.prototype.handle = function(record) {
        throw new Error("Unimplemented");
      };


      /*
        	 * @return {Function}
       */

      AbstractHandler.prototype.pushProcessor = function(processor) {
        this.processors.unshift(processor);
        return this;
      };


      /*
        	 * @return {Object}
       */

      AbstractHandler.prototype.popProcessor = function() {
        return this.processors.shift();
      };


      /*
        	 * Sets the formatter
        	 *
        	 * @param {Object} formatter
       */

      AbstractHandler.prototype.setFormatter = function(formatter) {
        this.formatter = formatter;
        return this;
      };


      /*
        	 * @return {Object}
       */

      AbstractHandler.prototype.getFormatter = function() {
        if (this.formatter === null) {
          this.formatter = this.getDefaultFormatter();
        }
        return this.formatter;
      };


      /*
        	 * @return {Object}
       */

      AbstractHandler.prototype.getDefaultFormatter = function() {
        return new LineFormatter();
      };


      /*
        	 * @return {Integer}
       */

      AbstractHandler.prototype.getLevel = function() {
        return this.level;
      };


      /*
        	 * Sets the level
        	 *
        	 * @param {Integer} level
       */

      AbstractHandler.prototype.setLevel = function(level) {
        this.level = level;
        return this;
      };


      /*
        	 * @return {bool}
       */

      AbstractHandler.prototype.getBubble = function() {
        return this.bubble;
      };


      /*
        	 * Enable/disable bubble
        	 *
        	 * @param {bool} bubble
       */

      AbstractHandler.prototype.setBubble = function(bubble) {
        this.bubble = bubble;
        return this;
      };

      return AbstractHandler;

    })();
  });

}).call(this);

//# sourceMappingURL=AbstractHandler.js.map
