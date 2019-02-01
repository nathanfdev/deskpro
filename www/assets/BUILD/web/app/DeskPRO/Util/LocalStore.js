define(['json3'], function(JSON) {
  /*
   * Thin wrapper around localStorage. Does nothing if localStorage is not supported.
   */
  class DeskPRO_Util_LocalStore {
    isSupported() {
      try {
        return (window['localStorage'] != null) && (window['localStorage'] !== null);
      } catch (e) {
        return false;
      }
    }

    /*
     * Get a value from local storage
     *
     * @param {String} k
     * @param mixed    default_val
     * @return {String}
     */
    get(k, default_val = null) {
      if (!this.isSupported()) { return null; }
      if (localStorage[k] != null) { return localStorage[k]; }
      return default_val;
    }


    /*
     * Set a value in local storage
     *
     * @param {String} k
     * @param {String} val
     * @return void
     */
    set(k, val) {
      if (!this.isSupported()) { return; }
      localStorage[k] = val;
    }


    /*
     * Check if a key exists in local storage
     *
     * @param {String} k
     * @return {Boolean}
     */
    has(k) {
      return this.isSupported() && (localStorage[k] != null);
    }


    /*
     * Remove something from localstorage
     *
     * @param {String} k
     * @return void
     */
    remove(k) {
      if (!this.isSupported()) { return; }
      localStorage[k] = null;
      delete localStorage[k];
    }


    /*
     * Set an object in local storage
     *
     * @param {String} k
     * @param {Object} val
     * @return void
     */
    setObject(k, val) {
      if (!this.isSupported()) { return; }
      localStorage[k] = JSON.stringify(val);
    }


    /*
     * Get an object from local storage
     *
     * @param {String} k
     * @param mixed    default_val
     * @return void
     */
    getObject(k, default_val) {
      let v = this.get(k);
      if (v === null) { return default_val; }

      try {
        v = JSON.parse(v);
      } catch (error) {
        return default_val;
      }

      return v;
    }
  }

  return new DeskPRO_Util_LocalStore();
});