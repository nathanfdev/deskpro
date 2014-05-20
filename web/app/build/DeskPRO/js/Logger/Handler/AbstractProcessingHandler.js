(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Util', 'DeskPRO/Logger/Handler/AbstractHandler'], function(Util, AbstractHandler) {
    var AbstractProcessingHandler;
    return AbstractProcessingHandler = (function(_super) {
      __extends(AbstractProcessingHandler, _super);

      function AbstractProcessingHandler() {
        return AbstractProcessingHandler.__super__.constructor.apply(this, arguments);
      }

      AbstractProcessingHandler.prototype.handle = function(record) {
        var formatter;
        if (!this.isHandling(record)) {
          return false;
        }
        record = Util.clone(record, false);
        record = this.processRecord(record);
        formatter = this.getFormatter();
        if (formatter) {
          if (formatter.format != null) {
            record.formatted = formatter.format(record);
          } else {
            record.formatted = formatter(record);
          }
        }
        this.write(record);
        return this.bubble === false;
      };

      AbstractProcessingHandler.prototype.write = function(record) {
        throw new Error("Unimplemented");
      };

      AbstractProcessingHandler.prototype.processRecord = function(record) {
        var proc, _i, _len, _ref;
        _ref = this.processors;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          proc = _ref[_i];
          if (proc.process != null) {
            record = proc.process(record);
          } else {
            record = proc(record);
          }
        }
        return record;
      };

      return AbstractProcessingHandler;

    })(AbstractHandler);
  });

}).call(this);

//# sourceMappingURL=AbstractProcessingHandler.js.map
