define(['DeskPRO/Util/Util'], (Util) => {
  class BaseService {
    constructor() {
      this.dp_spin_els = {};
    }

    /*
     * Enables a 'spinner' state in the view which will
     * last for at least minTime time.
     *
     * If a spinner already exists, then it will be restarted.
     *
     * @param {String} id The ID of the spinner
     * @param {Integer} minTime The min time the spinner should be visible for
     * @return {promise} A promise that resolves once the spinner stops
     */
    startSpinner(id, minTime) {
      if (minTime == null) { minTime = 1050; }
      if (!this.dp_spin_els) { this.dp_spin_els = {}; }

      if (this.dp_spin_els[id]) {
        this.stopSpinner(id, true);
      }

      const deferred = this.$q.defer();

      var desc = {
        doneTime:       false,
        doneSpin:       false,
        setTimeoutDone: () => {
          desc.doneTime = true;
          if (desc._timeout) {
            this.$timeout.cancel(desc._timeout);
          }

          if (desc.doneSpin) {
            return deferred.resolve();
          }
        },
        setSpinDone: () => {
          desc.doneSpin = true;
          if (desc.doneTime) {
            return deferred.resolve();
          }
        },
        _promise: deferred.promise,
        _timeout: this.$timeout(() => desc.setTimeoutDone()
        , minTime)
      };

      this.dp_spin_els[id] = desc;
      return desc._promise;
    }


    /*
     * Stops a 'spinner' state in the view. This by default
     * only marks the manual spinner state as off. The timer may stil
     * be going which means the spinner will still be visible until that
     * ends too. Pass force=true to stop the spinner (disregarding the min time)
     *
     * @param {String} id The ID of the spinner
     * @param {Boolean} force True to stop the spinner even if the minTime timer is still going
     * @return {promise} A promise that resolves once the spinner stops
     */
    stopSpinner(id, force) {
      if (force == null) { force = false; }
      if (!(this.dp_spin_els != null ? this.dp_spin_els[id] : undefined)) {
        const d = this.$q.defer();
        d.resolve();
        return d.promise();
      }

      this.dp_spin_els[id].setSpinDone();

      if (force) {
        this.dp_spin_els[id].setTimeoutDone();
      }

      return this.dp_spin_els[id]._promise;
    }
  }
  return BaseService;
});
