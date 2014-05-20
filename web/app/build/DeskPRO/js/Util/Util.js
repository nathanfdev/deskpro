(function() {
  var __hasProp = {}.hasOwnProperty,
    __slice = [].slice;

  define(['DeskPRO/Util/Strings'], function(Strings) {
    var DeskPRO_Util_Util;
    DeskPRO_Util_Util = (function() {
      DeskPRO_Util_Util.UID_COUNTER = 0;

      function DeskPRO_Util_Util() {
        this.optIsfunc = false;
        if (typeof /./ !== 'function') {
          this.optIsfunc = true;
        }
        this.nativeIsArray = false;
        if (Array.isArray != null) {
          this.nativeIsArray = true;
        }
        this.nativeObjKeys = false;
        if (Object.keys != null) {
          this.nativeObjKeys = true;
        }
      }


      /*
      		 * Gets a unique number for the current page
      		 *
      		 * @param {String} prefix Optional prefix
      		 * @return {String}
       */

      DeskPRO_Util_Util.prototype.uid = function(prefix) {
        if (prefix == null) {
          prefix = '';
        }
        DeskPRO_Util_Util.UID_COUNTER++;
        return prefix + DeskPRO_Util_Util.UID_COUNTER;
      };


      /*
      		 * Get a random number between min and max inclusive.
      		 *
      		 * @param {Integer} min
      		 * @param {Integer} max
      		 * @return {Integer}
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
      		 * Get an array of [key, value] in an object
      		 *
      		 * @param {Object} obj
      		 * @return {Array}
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
      		 * Get an array of keys in an object
      		 *
      		 * @param {Object} obj
      		 * @return {Array}
       */

      DeskPRO_Util_Util.prototype.keys = function(obj) {
        var k, keys, v;
        if (this.nativeObjKeys) {
          return obj.keys();
        } else {
          keys = [];
          for (k in obj) {
            if (!__hasProp.call(obj, k)) continue;
            v = obj[k];
            keys.push(k);
          }
          return keys;
        }
      };


      /*
      		 * Get an array of values in an object
      		 *
      		 * @param {Object} obj
      		 * @return {Array}
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
      		 * Check if a value is a function
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isFunction = function(obj) {
        if (this.optIsfunc) {
          return typeof obj === 'function';
        } else {
          return Object.prototype.toString.call(obj) === '[object Function]';
        }
      };


      /*
      		 * Check if a value is a string
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isString = function(obj) {
        return Object.prototype.toString.call(obj) === '[object String]';
      };


      /*
      		 * Check if a value is a string
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isBoolean = function(obj) {
        return typeof obj === 'boolean';
      };


      /*
      		 * Check if a value is an integer
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isInteger = function(obj) {
        return obj === parseInt(obj);
      };


      /*
      		 * Check if a value is an float
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isFloat = function(obj) {
        return obj === parseFloat(obj);
      };


      /*
      		 * Check if a value is a number
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isNumber = function(obj) {
        return typeof obj === 'number';
      };


      /*
      		 * Check if a value is undefined
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isUndefined = function(obj) {
        return typeof obj === 'undefined';
      };


      /*
      		 * Check if a value is empty (empty array, empty string, empty object, NaN).
        	 * Non-collection types like numbers and booleans are never considered empty.
        	 * If you need to catch things like an integer 0, use isBlank() instead.
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isEmpty = function(obj) {
        var k, v;
        if (obj === null) {
          return true;
        }
        if (this.isUndefined(obj)) {
          return true;
        }
        if (this.isArray(obj) && (obj.length != null)) {
          return obj.length === 0;
        }
        if (this.isString(obj) && (obj.length != null)) {
          return obj.length === 0;
        }
        if (this.isNumber(obj)) {
          return false;
        }
        if (this.isBoolean(obj)) {
          return false;
        }
        if (this.isFunction(obj)) {
          return false;
        }
        if (this.isNumber(obj) && isNaN(obj)) {
          return true;
        }
        for (k in obj) {
          if (!__hasProp.call(obj, k)) continue;
          v = obj[k];
          return false;
        }
        return true;
      };


      /*
      		 * Check if a value is blank. This means roughly the same as PHP's "falsey" values: 0, "0", [], ""
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isBlank = function(obj) {
        if (this.isEmpty(obj)) {
          return true;
        }
        if (this.isString(obj)) {
          obj = Strings.trim(obj);
          if (obj === "" || obj === "0") {
            return true;
          }
        }
        if (this.isNumber(obj)) {
          return obj === 0 || obj === 0.0;
        }
        if (this.isBoolean(obj)) {
          return !obj;
        }
        return false;
      };


      /*
      		 * Check if a value is an object
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isObject = function(obj) {
        return obj !== null && typeof obj === 'object';
      };


      /*
      		 * Check if a value is an array
      		 *
      		 * @param {Object} obj
      		 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.isArray = function(obj) {
        if (this.nativeIsArray) {
          return Array.isArray(obj);
        } else {
          return Object.prototype.toString.call(obj) === '[object Array]';
        }
      };


      /*
      		 * Copy properties from other_objects to destObj, returning destObj.
      		 *
      		 * @param {Object} destObj
      		 * @param {Object} other_objects...
      		 * @return {Object}
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
      		 * Merge all objects into a new object
      		 *
      		 * @param {Object} objects...
      		 * @return {Object}
       */

      DeskPRO_Util_Util.prototype.merge = function() {
        var args, objects;
        objects = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
        args = objects;
        args.unshift({});
        return this.extend.apply(this, args);
      };


      /*
      		 * Clones an object
      		 *
      		 * @param {Object} obj
      		 * @param {bool} deep True to do a deep clone
      		 * @return {Object}
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
            for (index = _i = 0, _len = result.length; _i < _len; index = ++_i) {
              value = result[index];
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


      /*
        	 * Compares two values to see if they are equal.
        	 *
        	 * If objects, every property of the object is compared with equals()
        	 *
        	 * @return {bool}
       */

      DeskPRO_Util_Util.prototype.equals = function(obj1, obj2, ignorePrivate) {
        var k, v, _i, _len;
        if (ignorePrivate == null) {
          ignorePrivate = true;
        }
        if (obj1 === obj2) {
          return true;
        }
        if (obj1 === null && obj2 === null) {
          return true;
        }
        if (obj1 !== obj1 && obj2 !== obj2) {
          return true;
        }
        if (this.isObject(obj1)) {
          if (this.isArray(obj1)) {
            if (!this.isArray(obj2)) {
              return false;
            }
            if (obj1.length !== obj2.length) {
              return false;
            }
            for (k = _i = 0, _len = obj1.length; _i < _len; k = ++_i) {
              v = obj1[k];
              if (!this.equals(obj1[k], obj2[k])) {
                return false;
              }
            }
            return true;
          } else {
            for (k in obj1) {
              if (!__hasProp.call(obj1, k)) continue;
              v = obj1[k];
              if (k === '__proto__' || k === 'prototype') {
                continue;
              }
              if (ignorePrivate) {
                if (k.substr(0, 1) === '_' || k.substr(0, 2) === '$$') {
                  continue;
                }
              }
              if (!this.equals(obj1[k], obj2[k])) {
                return false;
              }
            }
            for (k in obj2) {
              if (!__hasProp.call(obj2, k)) continue;
              v = obj2[k];
              if (k === '__proto__' || k === 'prototype') {
                continue;
              }
              if (ignorePrivate) {
                if (k.substr(0, 1) === '_' || k.substr(0, 2) === '$$') {
                  continue;
                }
              }
              if (!this.equals(obj1[k], obj2[k])) {
                return false;
              }
            }
            return true;
          }
        }
        return false;
      };


      /*
        	 * Dump a variable to a string repr
        	 *
        	 * @param {mixed} obj
        	 * @param {Integer} maxLvl How deep down nested structures to recurse
        	 * @return {String}
       */

      DeskPRO_Util_Util.prototype.dump = function(obj, maxLvl, _rlvl, _visited) {
        var k, out, subs, v, vis, _i, _len;
        if (maxLvl == null) {
          maxLvl = 5;
        }
        if (_rlvl == null) {
          _rlvl = 0;
        }
        if (_visited == null) {
          _visited = null;
        }
        out = '';
        out += Strings.repeat("\t", _rlvl);
        if (obj === null) {
          out += 'null';
        } else if (typeof obj === 'number' && isNaN(obj)) {
          out += 'NaN';
        } else if (this.isInteger(obj)) {
          out += "int:" + obj;
        } else if (this.isFloat(obj)) {
          out += "float:" + obj;
        } else if (this.isString(obj)) {
          out += "string:" + obj;
        } else if (this.isBoolean(obj)) {
          out += "bool:" + (obj ? "true" : "false");
        } else if (typeof obj === "undefined") {
          out += "undefined";
        } else if (typeof obj === "function") {
          out += "function";
        } else if (obj instanceof Date) {
          out += "Date(" + obj + ")";
        } else if (obj instanceof RegExp) {
          out += "RegExp(" + obj + ")";
        } else {
          if (_visited && _visited.indexOf(obj) !== -1) {
            out += "object(*RECURSION*)";
          } else {
            if (!_visited) {
              _visited = [];
            }
            _visited.push(obj);
            if (_rlvl >= maxLvl) {
              if (this.isArray(obj)) {
                out += "array:*MAX LEVEL REACHED*";
              } else {
                out += "object:*MAX LEVEL REACHED*";
              }
            } else {
              if (_visited && _visited.length) {
                vis = this.clone(_visited);
              } else {
                vis = [];
              }
              if (this.isArray(obj)) {
                out += "array:" + obj.length;
                if (obj.length) {
                  out += "\n";
                  for (_i = 0, _len = obj.length; _i < _len; _i++) {
                    v = obj[_i];
                    out += this.dump(v, maxLvl, _rlvl + 1, vis);
                    out += ",\n";
                  }
                }
              } else {
                out += "object:\n";
                for (k in obj) {
                  if (!__hasProp.call(obj, k)) continue;
                  v = obj[k];
                  subs = this.dump(v, maxLvl, _rlvl + 1, vis);
                  out += Strings.repeat("\t", _rlvl + 1) + ("" + k + ": ") + Strings.trim(subs) + ",\n";
                }
              }
            }
          }
        }
        return out;
      };

      return DeskPRO_Util_Util;

    })();
    return new DeskPRO_Util_Util();
  });

}).call(this);

//# sourceMappingURL=Util.js.map
