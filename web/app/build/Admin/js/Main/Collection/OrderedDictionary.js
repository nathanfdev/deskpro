(function() {
  define(['Admin/Main/Util/EventsMixin', 'DeskPRO/Util/Numbers'], function(EventsMixin, Numbers) {

    /**
    	* Save an ordered k=>v
     */
    var Admin_Main_Collection_OrderedDictionary;
    return Admin_Main_Collection_OrderedDictionary = (function() {
      function Admin_Main_Collection_OrderedDictionary() {
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

      Admin_Main_Collection_OrderedDictionary.prototype.clear = function() {
        var key, _i, _len, _ref;
        _ref = this.order;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          key = _ref[_i];
          delete this.data[key];
        }
        return this.order.length = 0;
      };


      /*
        	 * Re-order the collection with a callback comparison function.
        	 *
        	 * @param {Function} callback The callback function that returns 0, -1 or 1
       */

      Admin_Main_Collection_OrderedDictionary.prototype.reorder = function(callback) {
        callback = callback || this.orderFn;
        if (!callback) {
          return;
        }
        return this.order.sort((function(_this) {
          return function(k1, k2) {
            var v1, v2;
            v1 = _this.data[k1];
            v2 = _this.data[k2];
            return callback(v1, v2);
          };
        })(this));
      };


      /*
        	 * Count how many items are in the collection
        	 *
        	 * @return {Integer}
       */

      Admin_Main_Collection_OrderedDictionary.prototype.count = function() {
        return this.order.length;
      };


      /*
        	 * Add an array of objects, getting the key as id_prop from the object
        	 *
        	 * @param {Array} array The array of objects to add
        	 * @param {String} id_prop The property of th eobject to use as the key
       */

      Admin_Main_Collection_OrderedDictionary.prototype.addArray = function(array, id_prop) {
        var id, r, _i, _len, _results;
        if (id_prop == null) {
          id_prop = 'id';
        }
        _results = [];
        for (_i = 0, _len = array.length; _i < _len; _i++) {
          r = array[_i];
          id = r[id_prop];
          if (id != null) {
            _results.push(this.set(id, r));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };


      /*
        	 * Set a value in the collection
        	 *
        	 * @param {String} k The key to set
        	 * @param {mixed} v The value to set
        	 * @return {mixed} The v that was set
       */

      Admin_Main_Collection_OrderedDictionary.prototype.set = function(k, v) {
        var exist_pos;
        this._touch = (new Date()).getTime();
        if (Numbers.isNumber(k)) {
          k = parseInt(k);
        }
        this.data[k] = v;
        exist_pos = this.order.indexOf(k);
        if (exist_pos !== -1) {
          this.order.splice(exist_pos, 1);
        }
        this.order.push(k);
        this.reorder();
        this.notifyListeners('changed');
        return v;
      };


      /*
        	 * Get the value by key. If k does not exist, returns default_val
        	 *
        	 * @param {String} k The key to get
        	 * @param {mixed} default_val The value to return if k does not exist
        	 * @return {mixed}
       */

      Admin_Main_Collection_OrderedDictionary.prototype.get = function(k, default_val) {
        if (default_val == null) {
          default_val = null;
        }
        if (Numbers.isNumber(k)) {
          k = parseInt(k);
        }
        if (this.data[k] == null) {
          return default_val;
        }
        return this.data[k];
      };


      /*
        	 * Remove a key from the collection
        	 *
        	 * @param {String} k The key to remove
        	 * @return {mixed} The value removed, or null if no key
       */

      Admin_Main_Collection_OrderedDictionary.prototype.remove = function(k) {
        var exist_pos, val;
        this._touch = (new Date()).getTime();
        if (Numbers.isNumber(k)) {
          k = parseInt(k);
        }
        val = null;
        if (this.data[k] != null) {
          val = this.data[k];
          delete this.data[k];
          exist_pos = this.order.indexOf(k);
          this.order.splice(exist_pos, 1);
          this.reorder();
          this.notifyListeners('changed');
        }
        return val;
      };


      /*
        	 * Check if collection contains a key
        	 *
        	 * @param {String} k
        	 * @return {Booleab}
       */

      Admin_Main_Collection_OrderedDictionary.prototype.has = function(k) {
        return !(this.data[k] == null);
      };


      /*
        	 * Call fn over every key,val of the collection
        	 *
        	 * @param {Function} A function that should accept two args: key and val. Return false to stop the loop.
        	 * @return void
       */

      Admin_Main_Collection_OrderedDictionary.prototype.forEach = function(fn) {
        var key, ret, val, _i, _len, _ref, _results;
        _ref = this.order;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          key = _ref[_i];
          val = this.data[key];
          ret = fn(key, val);
          if (ret === false) {
            break;
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };


      /*
        	 * Get an ordered array of [key, val]
        	 *
        	 * @return {Array}
       */

      Admin_Main_Collection_OrderedDictionary.prototype.getOrderedPair = function() {
        var key, ret, val, _i, _len, _ref;
        ret = [];
        _ref = this.order;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          key = _ref[_i];
          val = this.data[key];
          ret.push([key, val]);
        }
        return ret;
      };


      /*
        	 * Get collection keys as an ordered array
        	 *
        	 * @return {Array}
       */

      Admin_Main_Collection_OrderedDictionary.prototype.keys = function() {
        return this.order;
      };


      /*
        	 * Get the collection as an array
        	 *
        	 * @return {Array}
       */

      Admin_Main_Collection_OrderedDictionary.prototype.values = function() {
        var key, ret, val, _i, _len, _ref;
        ret = [];
        _ref = this.order;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          key = _ref[_i];
          val = this.data[key];
          ret.push(val);
        }
        return ret;
      };

      return Admin_Main_Collection_OrderedDictionary;

    })();
  });

}).call(this);

//# sourceMappingURL=OrderedDictionary.js.map
