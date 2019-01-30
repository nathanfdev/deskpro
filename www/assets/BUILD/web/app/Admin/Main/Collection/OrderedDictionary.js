/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Util/EventsMixin', 'DeskPRO/Util/Numbers'], function(EventsMixin, Numbers) {
  /**
  * Save an ordered k=>v
  */
  let Admin_Main_Collection_OrderedDictionary;
  return (Admin_Main_Collection_OrderedDictionary = class Admin_Main_Collection_OrderedDictionary {
    constructor() {
      EventsMixin(this);
      this._touch = (new Date()).getTime();
      this.scope = null;
      this.data = {};
      this.order = [];
      this.orderFn = null;
    }

    /*
      * Clears all data from the collection
      */
    clear() {
      for (let key of Array.from(this.order)) {
        delete this.data[key];
      }
      return this.order.length = 0;
    }


    /*
      * Re-order the collection with a callback comparison function.
      *
      * @param {Function} callback The callback function that returns 0, -1 or 1
      */
    reorder(callback) {
      callback = callback || this.orderFn;
      if (!callback) { return; }

      return this.order.sort( (k1, k2) => {
        const v1 = this.data[k1];
        const v2 = this.data[k2];

        return callback(v1, v2);
      });
    }


    /*
      * Count how many items are in the collection
      *
      * @return {Integer}
      */
    count() {
      return this.order.length;
    }


    /*
      * Add an array of objects, getting the key as id_prop from the object
      *
      * @param {Array} array The array of objects to add
      * @param {String} id_prop The property of th eobject to use as the key
      */
    addArray(array, id_prop) {
      if (id_prop == null) { id_prop = 'id'; }
      return (() => {
        const result = [];
        for (let r of Array.from(array)) {
          const id = r[id_prop];
          if (id != null) { result.push(this.set(id, r)); } else {
            result.push(undefined);
          }
        }
        return result;
      })();
    }


    /*
      * Set a value in the collection
      *
      * @param {String} k The key to set
      * @param {mixed} v The value to set
      * @return {mixed} The v that was set
      */
    set(k, v) {
      this._touch = (new Date()).getTime();

      if (Numbers.isNumber(k)) { k = parseInt(k); }

      this.data[k] = v;

      const exist_pos = this.order.indexOf(k);
      if (exist_pos !== -1) {
        this.order.splice(exist_pos, 1);
      }

      this.order.push(k);
      this.reorder();
      this.notifyListeners('changed');
      return v;
    }


    /*
      * Get the value by key. If k does not exist, returns default_val
      *
      * @param {String} k The key to get
      * @param {mixed} default_val The value to return if k does not exist
      * @return {mixed}
      */
    get(k, default_val = null) {
      if (Numbers.isNumber(k)) { k = parseInt(k); }
      if ((this.data[k] == null)) {
        return default_val;
      }

      return this.data[k];
    }


    /*
      * Remove a key from the collection
      *
      * @param {String} k The key to remove
      * @return {mixed} The value removed, or null if no key
      */
    remove(k) {
      this._touch = (new Date()).getTime();
      if (Numbers.isNumber(k)) { k = parseInt(k); }

      let val = null;
      if (this.data[k] != null) {
        val = this.data[k];
        delete this.data[k];
        const exist_pos = this.order.indexOf(k);
        this.order.splice(exist_pos, 1);
        this.reorder();
        this.notifyListeners('changed');
      }

      return val;
    }


    /*
      * Check if collection contains a key
      *
      * @param {String} k
      * @return {Booleab}
      */
    has(k) {
      return !(this.data[k] == null);
    }


    /*
      * Call fn over every key,val of the collection
      *
      * @param {Function} A function that should accept two args: key and val. Return false to stop the loop.
      * @return void
      */
    forEach(fn) {
      return (() => {
        const result = [];
        for (let key of Array.from(this.order)) {
          const val = this.data[key];
          const ret = fn(key, val);
          if (ret === false) {
            break;
          } else {
            result.push(undefined);
          }
        }
        return result;
      })();
    }


    /*
      * Get an ordered array of [key, val]
      *
      * @return {Array}
      */
    getOrderedPair() {
      const ret = [];
      for (let key of Array.from(this.order)) {
        const val = this.data[key];
        ret.push([key, val]);
      }

      return ret;
    }


    /*
      * Get collection keys as an ordered array
      *
      * @return {Array}
      */
    keys() {
      return this.order;
    }


    /*
      * Get the collection as an array
      *
      * @return {Array}
    */
    values() {
      const ret = [];
      for (let key of Array.from(this.order)) {
        const val = this.data[key];
        ret.push(val);
      }

      return ret;
    }
  });
});