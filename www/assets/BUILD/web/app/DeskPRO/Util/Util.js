define([
  'DeskPRO/Util/Strings'
], function(
  Strings
) {
  class DeskPRO_Util_Util {
    static initClass() {
      this.UID_COUNTER = 0;
    }

    constructor() {
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
    uid(prefix) {
      if (prefix == null) { prefix = ''; }
      DeskPRO_Util_Util.UID_COUNTER++;
      return prefix + DeskPRO_Util_Util.UID_COUNTER;
    }

    /*
     * Get a random number between min and max inclusive.
     *
     * @param {Integer} min
     * @param {Integer} max
     * @return {Integer}
     */
    random(min, max = null) {
      if (max === null) {
        max = min;
        min = 0;
      }

      return min + Math.floor(Math.random() * ((max - min) + 1));
    }

    /*
     * Get an array of [key, value] in an object
     *
     * @param {Object} obj
     * @return {Array}
     */
    keyValuePair(obj) {
      const pairs = [];
      for (let k of Object.keys(obj || {})) {
        const v = obj[k];
        pairs.push([k, v]);
      }

      return pairs;
    }


    /*
     * Get an array of keys in an object
     *
     * @param {Object} obj
     * @return {Array}
     */
    keys(obj) {
      if (this.nativeObjKeys) {
        return obj.keys();
      } else {
        const keys = [];
        for (let k of Object.keys(obj || {})) {
          const v = obj[k];
          keys.push(k);
        }

        return keys;
      }
    }


    /*
     * Get an array of values in an object
     *
     * @param {Object} obj
     * @return {Array}
     */
    values(obj) {
      const values = [];
      for (let k of Object.keys(obj || {})) {
        const v = obj[k];
        values.push(v);
      }

      return values;
    }


    /*
     * Check if a value is a function
     *
     * @param {Object} obj
     * @return {bool}
     */
    isFunction(obj) {
      if (this.optIsfunc) {
        return typeof obj === 'function';
      } else {
        return Object.prototype.toString.call(obj) === '[object Function]';
      }
    }


    /*
     * Check if a value is a string
     *
     * @param {Object} obj
     * @return {bool}
     */
    isString(obj) {
      return Object.prototype.toString.call(obj) === '[object String]';
    }


    /*
     * Check if a value is a string
     *
     * @param {Object} obj
     * @return {bool}
     */
    isBoolean(obj) {
      return typeof obj === 'boolean';
    }


    /*
     * Check if a value is an integer
     *
     * @param {Object} obj
     * @return {bool}
     */
    isInteger(obj) {
      return obj === parseInt(obj);
    }


    /*
     * Check if a value is an float
     *
     * @param {Object} obj
     * @return {bool}
     */
    isFloat(obj) {
      return obj === parseFloat(obj);
    }


    /*
     * Check if a value is a number
     *
     * @param {Object} obj
     * @return {bool}
     */
    isNumber(obj) {
      return typeof obj === 'number';
    }


    /*
     * Check if a value is undefined
     *
     * @param {Object} obj
     * @return {bool}
     */
    isUndefined(obj) {
      return typeof obj === 'undefined';
    }

    /*
    * Check if a value is empty (empty array, empty string, empty object, NaN).
      * Non-collection types like numbers and booleans are never considered empty.
      * If you need to catch things like an integer 0, use isBlank() instead.
    *
    * @param {Object} obj
    * @return {bool}
    */
    isEmpty(obj) {
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
      if (obj instanceof Date) {
        return false;
      }

      for (let k of Object.keys(obj || {})) {
        const v = obj[k];
        return false;
      }

      return true;
    }


    /*
      * Check if an object is empty or contains only empty data
      *
      * @param {Object} obj
      * @return {bool}
      */
    isBlankObject(obj) {
      let any = false;
      for (let k of Object.keys(obj || {})) {
        const v = obj[k];
        if (v) {
          if (this.isString(v)) {
            if (v.length) {
              any = true;
              break;
            }
          } else {
            any = true;
            break;
          }
        }
      }

      return !any;
    }


    /*
     * Check if a value is blank. This means roughly the same as PHP's "falsey" values: 0, "0", [], ""
     *
     * @param {Object} obj
     * @return {bool}
     */
    isBlank(obj) {
      if (this.isEmpty(obj)) { return true; }

      if (this.isString(obj)) {
        obj = Strings.trim(obj);
        if ((obj === "") || (obj === "0")) { return true; }
      }

      if (this.isNumber(obj)) {
        return ((obj === 0) || (obj === 0.0));
      }

      if (this.isBoolean(obj)) {
        return !obj;
      }

      return false;
    }


    /*
     * Check if a value is an object
     *
     * @param {Object} obj
     * @return {bool}
     */
    isObject(obj) {
      return (obj !== null) && (typeof obj === 'object');
    }


    /*
     * Check if a value is an array
     *
     * @param {Object} obj
     * @return {bool}
     */
    isArray(obj) {
      if (this.nativeIsArray) {
        return Array.isArray(obj);
      } else {
        return Object.prototype.toString.call(obj) === '[object Array]';
      }
    }


    /*
     * Copy properties from other_objects to destObj, returning destObj.
     *
     * @param {Object} destObj
     * @param {Object} other_objects...
     * @return {Object}
     */
    extend(destObj, ...other_objects) {
      for (let other_obj of Array.from(other_objects)) {
        for (let k of Object.keys(other_obj || {})) {
          const v = other_obj[k];
          destObj[k] = v;
        }
      }

      return destObj;
    }


    /*
     * Merge all objects into a new object
     *
     * @param {Object} objects...
     * @return {Object}
     */
    merge(...objects) {
      const args = objects;
      args.unshift({});
      return this.extend.apply(this, args);
    }


    /*
     * Clones an object
     *
     * @param {Object} obj
     * @param {bool} deep True to do a deep clone
     * @return {Object}
     */
    clone(obj, deep) {
      let result, value;
      if (deep == null) { deep = false; }
      if (!this.isObject(obj)) { return obj; }
      if (this.isArray(obj)) {
        result = obj.slice(0);
        if (deep) {
          for (let index = 0; index < result.length; index++) {
            value = result[index];
            result[index] = this.clone(value, true);
          }
        }
      } else {
        result = {};
        for (let key of Object.keys(obj || {})) {
          value = obj[key];
          if (deep) {
            result[key] = this.clone(value, true);
          } else {
            result[key] = value;
          }
        }
      }

      return result;
    }


    /*
     * Compares two values to see if they are equal.
     *
     * If objects, every property of the object is compared with equals()
     *
     * @return {bool}
     */
    equals(obj1, obj2, ignorePrivate) {
      if (ignorePrivate == null) { ignorePrivate = true; }
      if (obj1 === obj2) {
        return true;
      }

      if ((obj1 === null) && (obj2 === null)) {
        return true;
      }

      // NaN
      if ((obj1 !== obj1) && (obj2 !== obj2)) {
        return true;
      }

      if (this.isObject(obj1)) {
        let k, v;
        if (this.isArray(obj1)) {
          if (!this.isArray(obj2)) {
            return false;
          }

          if (obj1.length !== obj2.length) {
            return false;
          }

          for (k = 0; k < obj1.length; k++) {
            v = obj1[k];
            if (!this.equals(obj1[k], obj2[k])) {
              return false;
            }
          }

          return true;
        } else {
          for (k of Object.keys(obj1 || {})) {
            v = obj1[k];
            if ((k === '__proto__') || (k === 'prototype')) { continue; }
            if (ignorePrivate) {
              if ((k.substr(0, 1) === '_') || (k.substr(0, 2) === '$$')) { continue; }
            }

            if (!this.equals(obj1[k], obj2[k])) {
              return false;
            }
          }
          for (k of Object.keys(obj2 || {})) {
            v = obj2[k];
            if ((k === '__proto__') || (k === 'prototype')) { continue; }
            if (ignorePrivate) {
              if ((k.substr(0, 1) === '_') || (k.substr(0, 2) === '$$')) { continue; }
            }

            if (!this.equals(obj1[k], obj2[k])) {
              return false;
            }
          }

          return true;
        }
      }

      return false;
    }

    /*
     * Dump a variable to a string repr
     *
     * @param {mixed} obj
     * @param {Integer} maxLvl How deep down nested structures to recurse
     * @return {String}
     */
    dump(obj, maxLvl, _rlvl, _visited = null) {
      if (maxLvl == null) { maxLvl = 5; }
      if (_rlvl == null) { _rlvl = 0; }
      let out = '';
      out += Strings.repeat("\t", _rlvl);

      if (obj === null) {
        out += 'null';
      } else if ((typeof obj === 'number') && isNaN(obj)) {
        out += 'NaN';
      } else if (this.isInteger(obj)) {
        out += `int:${obj}`;
      } else if (this.isFloat(obj)) {
        out += `float:${obj}`;
      } else if (this.isString(obj)) {
        out += `string:${obj}`;
      } else if (this.isBoolean(obj)) {
        out += `bool:${obj ? "true" : "false"}`;
      } else if (typeof obj === "undefined") {
        out += "undefined";
      } else if (typeof obj === "function") {
        out += "function";
      } else if (obj instanceof Date) {
        out += `Date(${obj})`;
      } else if (obj instanceof RegExp) {
        out += `RegExp(${obj})`;
      } else {
        if (_visited && (_visited.indexOf(obj) !== -1)) {
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
            let v, vis;
            if (_visited && _visited.length) {
              vis = this.clone(_visited);
            } else {
              vis = [];
            }

            if (this.isArray(obj)) {
              out += `array:${obj.length}`;
              if (obj.length) {
                out += "\n";
                for (v of Array.from(obj)) {
                  out += this.dump(v, maxLvl, _rlvl+1, vis);
                  out += ",\n";
                }
              }
            } else {
              out += "object:\n";
              for (let k of Object.keys(obj || {})) {
                v = obj[k];
                const subs = this.dump(v, maxLvl, _rlvl+1, vis);
                out += Strings.repeat("\t", _rlvl+1) + `${k}: ` + Strings.trim(subs) + ",\n";
              }
            }
          }
        }
      }

      return out;
    }
  }
  DeskPRO_Util_Util.initClass();

  return new DeskPRO_Util_Util();
});