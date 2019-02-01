define(function() {
  class InterfaceTimer {
    constructor(logger) {
      this.logger = logger;
      this.lastController = null;
      this.isWithinLoad = false;
      this.digestStart = null;
      this.digestEnd = null;
    }

    startControllerLoad(controller) {
      this.lastController = controller;
      return this.isWithinLoad = true;
    }

    startDigest() {
      return this.digestStart = new Date();
    }

    endDigest() {
      this.digestEnd = new Date();

      if (this.isWithinLoad) {
        let level = null;
        const time = this.digestEnd.getTime() - this.digestStart.getTime();
        if (time > 500) {
          level = 'notice';
        }
        if (time > 750) {
          level = 'warning';
        }

        if (level) {
          this.logger[level]([`[InterfaceTimer] (${this.lastController.constructor.CTRL_ID}) Load Digest Time: {0}ms`, time]);
        }

        return this.isWithinLoad = false;
      }
    }

    endControllerLoad() {
    }
  }
  return InterfaceTimer;
});