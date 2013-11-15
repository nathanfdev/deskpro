(function() {
  define(function() {
    var InterfaceTimer;
    return InterfaceTimer = (function() {
      function InterfaceTimer(logger) {
        this.logger = logger;
        this.lastController = null;
        this.isWithinLoad = false;
        this.digestStart = null;
        this.digestEnd = null;
      }

      InterfaceTimer.prototype.startControllerLoad = function(controller) {
        this.lastController = controller;
        return this.isWithinLoad = true;
      };

      InterfaceTimer.prototype.startDigest = function() {
        return this.digestStart = new Date();
      };

      InterfaceTimer.prototype.endDigest = function() {
        var level, time;
        this.digestEnd = new Date();
        if (this.isWithinLoad) {
          level = 'info';
          time = this.digestEnd.getTime() - this.digestStart.getTime();
          if (time > 500) {
            level = 'warning';
          }
          this.logger[level](["[InterfaceTimer] (" + this.lastController.constructor.CTRL_ID + ") Load Digest Time: %dms", time]);
          return this.isWithinLoad = false;
        }
      };

      InterfaceTimer.prototype.endControllerLoad = function() {};

      return InterfaceTimer;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=InterfaceTimer.js.map
*/