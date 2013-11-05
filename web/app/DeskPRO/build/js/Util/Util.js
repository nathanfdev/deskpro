(function() {
  var __hasProp = {}.hasOwnProperty,
    __slice = [].slice;

  define(function() {
    var DeskPRO_Util_Util;
    DeskPRO_Util_Util = (function() {
      DeskPRO_Util_Util.UID_COUNTER = 0;

      function DeskPRO_Util_Util() {
        this.optIsfunc = false;
        if (typeof /./ !== 'function') {
          this.optIsfunc = true;
        }
      }

      /*
        	# Gets a unique number for the current page
        	#
        	# @param {String} prefix Optional prefix
        	# @return {String}
      */


      DeskPRO_Util_Util.prototype.uid = function(prefix) {
        if (prefix == null) {
          prefix = '';
        }
        DeskPRO_Util_Util.UID_COUNTER++;
        return prefix + DeskPRO_Util_Util.UID_COUNTER;
      };

      /*
        	# Get a random number between min and max inclusive.
        	#
        	# @param {Integer} min
        	# @param {Integer} max
        	# @return {Integer}
      */


      DeskPRO_Util_Util.prototype.random = function(min, max) {
        if (max == null) {
          max = null;
        }
        if (max === null) {
          max = min;
          min = 0;
        }
        return min + Math.floor(Math.random() * (max - min + 1));
      };

      /*
      		# Get an array of [key, value] in an object
        	#
        	# @param {Object} obj
        	# @return {Array}
      */


      DeskPRO_Util_Util.prototype.keyValuePair = function(obj) {
        var k, pairs, v;
        pairs = [];
        for (k in obj) {
          if (!__hasProp.call(obj, k)) continue;
          v = obj[k];
          pairs.push([k, v]);
        }
        return pairs;
      };

      /*
      		# Get an array of keys in an object
        	#
        	# @param {Object} obj
        	# @return {Array}
      */


      if (Object.keys != null) {
        ({
          keys: function(obj) {
            return obj.keys();
          }
        });
      } else {
        ({
          keys: function(obj) {
            var k, keys, v;
            keys = [];
            for (k in obj) {
              if (!__hasProp.call(obj, k)) continue;
              v = obj[k];
              keys.push(k);
            }
            return keys;
          }
        });
      }

      /*
      		# Get an array of values in an object
        	#
        	# @param {Object} obj
        	# @return {Array}
      */


      DeskPRO_Util_Util.prototype.values = function(obj) {
        var k, v, values;
        values = [];
        for (k in obj) {
          if (!__hasProp.call(obj, k)) continue;
          v = obj[k];
          values.push(v);
        }
        return values;
      };

      /*
      		# Check if a value is a function
        	#
        	# @param {Object} obj
        	# @return {bool}
      */


      DeskPRO_Util_Util.prototype.isFunction = function(obj) {
        if (this.optIsfunc) {
          return typeof obj === 'function';
        } else {
          return Object.prototype.toString.call(obj) === '[object Function]';
        }
      };

      /*
      		# Check if a value is a string
      		#
      		# @param {Object} obj
      		# @return {bool}
      */


      DeskPRO_Util_Util.prototype.isString = function(obj) {
        return Object.prototype.toString.call(obj) === '[object String]';
      };

      /*
      		# Check if a value is empty (empty array, empty string, empty object)
      		#
      		# @param {Object} obj
      		# @return {bool}
      */


      DeskPRO_Util_Util.prototype.isEmpty = function(obj) {
        var k, v;
        if (obj === null) {
          return true;
        }
        if (this.isArray(obj) && obj.length) {
          return obj.length === 0;
        }
        if (this.isString(obj) && val.length) {
          return val.length === 0;
        }
        for (k in obj) {
          if (!__hasProp.call(obj, k)) continue;
          v = obj[k];
          return false;
        }
        return true;
      };

      /*
      		# Check if a value is an object
      		#
      		# @param {Object} obj
      		# @return {bool}
      */


      DeskPRO_Util_Util.prototype.isObject = function(obj) {
        return obj === Object(obj);
      };

      /*
      		# Check if a value is an array
      		#
      		# @param {Object} obj
      		# @return {bool}
      */


      if (Array.isArray != null) {
        ({
          isArray: function(obj) {
            return Array.isArray(obj);
          }
        });
      } else {
        ({
          isArray: function(obj) {
            return Object.prototype.toString.call(obj) === '[object Array]';
          }
        });
      }

      /*
        	# Copy properties from other_objects to destObj, returning destObj.
        	#
        	# @param {Object} destObj
        	# @param {Object} other_objects...
        	# @return {Object}
      */


      DeskPRO_Util_Util.prototype.extend = function() {
        var destObj, k, other_obj, other_objects, v, _i, _len;
        destObj = arguments[0], other_objects = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
        for (_i = 0, _len = other_objects.length; _i < _len; _i++) {
          other_obj = other_objects[_i];
          for (k in other_obj) {
            if (!__hasProp.call(other_obj, k)) continue;
            v = other_obj[k];
            destObj[k] = v;
          }
        }
        return destObj;
      };

      /*
        	# Merge all objects into a new object
        	#
        	# @param {Object} objects...
        	# @return {Object}
      */


      DeskPRO_Util_Util.prototype.merge = function() {
        var args, objects;
        objects = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
        args = objects;
        args.unshift({});
        return this.extend.apply(this, args);
      };

      /*
        	# Clones an object
        	#
        	# @param {Object} obj
        	# @param {bool} deep True to do a deep clone
        	# @return {Object}
      */


      DeskPRO_Util_Util.prototype.clone = function(obj, deep) {
        var index, key, result, value, _i, _len;
        if (deep == null) {
          deep = false;
        }
        if (!this.isObject(obj)) {
          return obj;
        }
        if (this.isArray(obj)) {
          result = obj.slice(0);
          if (deep) {
            for (value = _i = 0, _len = result.length; _i < _len; value = ++_i) {
              index = result[value];
              result[index] = this.clone(value, true);
            }
          }
        } else {
          result = {};
          for (key in obj) {
            if (!__hasProp.call(obj, key)) continue;
            value = obj[key];
            if (deep) {
              result[key] = this.clone(value, true);
            } else {
              result[key] = value;
            }
          }
        }
        return result;
      };

      return DeskPRO_Util_Util;

    })();
    return new DeskPRO_Util_Util();
  });

}).call(this);

/*
//@ sourceMappingURL=Util.js.map
*/