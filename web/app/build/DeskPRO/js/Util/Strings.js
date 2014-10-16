(function() {
  var __slice = [].slice;

  define(function() {
    var DeskPRO_Util_Strings;
    DeskPRO_Util_Strings = (function() {
      function DeskPRO_Util_Strings() {}

      DeskPRO_Util_Strings.CHARS_ALPHANUM = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

      DeskPRO_Util_Strings.CHARS_ALPHANUM_I = '0123456789abcdefghijklmnopqrstuvwxyz';

      DeskPRO_Util_Strings.CHARS_ALPHANUM_IU = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

      DeskPRO_Util_Strings.CHARS_NUM = '0123456789';

      DeskPRO_Util_Strings.CHARS_ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

      DeskPRO_Util_Strings.CHARS_ALPHA_I = 'abcdefghijklmnopqrstuvwxyz';

      DeskPRO_Util_Strings.CHARS_ALPHA_IU = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

      DeskPRO_Util_Strings.CHARS_SECURE = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()-_=+{}|[]:;,./<>?';

      DeskPRO_Util_Strings.CHARS_KEY = '23456789ABCDGHJKMNPQRSTWXYZ';

      DeskPRO_Util_Strings.CHARS_KEY_ALPHA = 'ABCDGHJKMNPQRSTWXYZ';

      DeskPRO_Util_Strings.CHARS_KEY_NUM = '23456789';

      DeskPRO_Util_Strings.ENTITY_MAP = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
      };

      DeskPRO_Util_Strings.TPL_MATCHER = /<%-([\s\S]+?)%>|<%=([\s\S]+?)%>|<%([\s\S]+?)%>|$/g;

      DeskPRO_Util_Strings.TPL_ESCAPES = {
        "'": "'",
        '\\': '\\',
        '\r': 'r',
        '\n': 'n',
        '\t': 't',
        '\u2028': 'u2028',
        '\u2029': 'u2029'
      };

      DeskPRO_Util_Strings.TPL_ESCAPER = /\\|'|\r|\n|\t|\u2028|\u2029/g;


      /*
        	 * Generates a random string.
        	 *
        	 * @param {Integer} len     How long the generated string should be
        	 * @param {String}  chars   A string of characters to choose form, or the name of a preset
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.random = function(len, chars) {
        var charsSet, i, maxRange, rnd, string, _i;
        if (len == null) {
          len = 8;
        }
        if (chars == null) {
          chars = null;
        }
        if (!chars) {
          chars = DeskPRO_Util_Strings.CHARS_ALPHANUM;
        } else {
          charsSet = "CHARS_" + chars.toUpperCase();
          if (DeskPRO_Util_Strings[charsSet] != null) {
            chars = DeskPRO_Util_Strings[charsSet];
          }
        }
        string = "";
        maxRange = chars.len - 1;
        for (i = _i = 0; 0 <= len ? _i <= len : _i >= len; i = 0 <= len ? ++_i : --_i) {
          rnd = Math.floor(Math.random() * maxRange + 1);
          string += chars.charAt(rnd);
        }
        return string;
      };


      /*
        	 * Removes leading and trailing whitespace
        	 *
        	 * @param {String} string
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.trim = function(string) {
        if (string.trim != null) {
          return string.trim();
        }
        return string.replace(/^\s+|\s+$/g, '');
      };


      /*
        	 * Removes leading whitespace
      
        	 * @param {String} string
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.trimLeft = function(string) {
        if (string.trimLeft != null) {
          return string.trimLeft();
        }
        return string.replace(/^\s+/, '');
      };


      /*
        	 * Removes trailing whitespace
        	 *
        	 * @param {String} string
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.trimRight = function(string) {
        if (string.trimRight != null) {
          return string.trimRight();
        }
        return string.replace(/\s+$/, '');
      };


      /*
        	 * Checks if a string is blank (empty after trimming leading and trailing whitespace)
        	 *
        	 * @param {String} string
        	 * @return {Boolean}
       */

      DeskPRO_Util_Strings.prototype.isBlank = function(string) {
        if (string === "") {
          return true;
        }
        return this.trim(string) === "";
      };


      /*
        	 * Given a string with words separated by dashes, underscores or spaces, convert it into
        	 * camel case. For example "my-string" and "my_string" becomes myString
        	 *
        	 * @param {String} string
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.toCamelCase = function(string) {
        return string.toLowerCase().replace(/[\-_ ]{1}([a-zA-Z])/g, function(match, group1) {
          return group1.toUpperCase();
        });
      };


      /*
        	 * Uppercase the first letter of a string
        	 *
        	 * @param {String} string
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.ucFirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      };


      /*
        	 * Escape special regex chars in a string
        	 *
        	 * @param {String} regexString
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.escapeRegex = function(regexString) {
        return regexString.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
      };


      /*
        	 * Escape special HTML chars in a string
        	 *
        	 * @param {String} htmlString
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.escapeHtml = function(htmlString) {
        return (htmlString + '').replace(/[&<>"'\/]/g, function(s) {
          return DeskPRO_Util_Strings.ENTITY_MAP[s];
        });
      };


      /*
        	 * Repeat a str num times
        	 *
        	 * @param {String} str
        	 * @param {Integer} num
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.repeat = function(str, num) {
        var res;
        if (num < 1) {
          return '';
        }
        res = '';
        while (num-- > 0) {
          res += str;
        }
        return res;
      };


      /*
        	 * Simple formatter replaces {0}, {1} etc in a string with args passed.
        	 *
        	 * @param {String} str
        	 * @param {mixed} args...  Args to place back in to str
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.format = function() {
        var args, str;
        str = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
        return str.replace(/\{(\d+)\}/g, function(m, num) {
          if (args[num] != null) {
            return args[num];
          } else {
            return m;
          }
        });
      };


      /*
        	 * Pad the start of a string with padStr until it is len characters long.
        	 *
        	 * @param {String} str
        	 * @param {String} padStr
        	 * @param {Integer} len
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.prePad = function(str, padStr, len) {
        if (len == null) {
          len = 2;
        }
        str = str + "";
        padStr = padStr + "";
        while (str.length < len) {
          str = str + padStr;
        }
        if (str.length > len) {
          return str.substring(0, len);
        }
        return str;
      };


      /*
        	 * Pad the end of a string with padStr until it is len characters long.
        	 *
        	 * @param {String} str
        	 * @param {String} padStr
        	 * @param {Integer} len
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.pad = function(str, padStr, len) {
        if (len == null) {
          len = 2;
        }
        str = str + "";
        padStr = padStr + "";
        while (str.length < len) {
          str += padStr;
        }
        if (str.length > len) {
          return str.substring(0, len);
        }
        return str;
      };


      /*
        	 * Escapes control characters in strings
        	 *
        	 * @param {String} str
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.addslashes = function(str) {
        return str.replace(/\\/g, '\\\\').replace(/\u0008/g, '\\b').replace(/\t/g, '\\t').replace(/\n/g, '\\n').replace(/\f/g, '\\f').replace(/\r/g, '\\r').replace(/'/g, '\\\'').replace(/"/g, '\\"');
      };


      /*
        	 * Generate a MurmurHash3 hash. This is a very very fast non-crypto hash (eg can be used for hash tables etc)
        	 *
        	 * See: https://github.com/garycourt/murmurhash-js
        	 *
        	 * @param {String} key
        	 * @param {String} seed
        	 * @return {String}
       */

      DeskPRO_Util_Strings.prototype.murmurhash3 = function(key, seed, asHex) {
        var bytes, c1, c2, h1, h1b, i, k1, remainder, res;
        if (asHex == null) {
          asHex = true;
        }
        remainder = key.length & 3;
        bytes = key.length - remainder;
        h1 = seed;
        c1 = 0xcc9e2d51;
        c2 = 0x1b873593;
        i = 0;
        while (i < bytes) {
          k1 = (key.charCodeAt(i) & 0xff) | ((key.charCodeAt(++i) & 0xff) << 8) | ((key.charCodeAt(++i) & 0xff) << 16) | ((key.charCodeAt(++i) & 0xff) << 24);
          ++i;
          k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
          k1 = (k1 << 15) | (k1 >>> 17);
          k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
          h1 ^= k1;
          h1 = (h1 << 13) | (h1 >>> 19);
          h1b = (((h1 & 0xffff) * 5) + ((((h1 >>> 16) * 5) & 0xffff) << 16)) & 0xffffffff;
          h1 = ((h1b & 0xffff) + 0x6b64) + ((((h1b >>> 16) + 0xe654) & 0xffff) << 16);
        }
        k1 = 0;
        if (remainder === 3) {
          k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16;
        }
        if (remainder === 3 || remainder === 2) {
          k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8;
        }
        if (remainder === 3 || remainder === 2 || remainder === 1) {
          k1 ^= key.charCodeAt(i) & 0xff;
          k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
          k1 = (k1 << 15) | (k1 >>> 17);
          k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
          h1 ^= k1;
        }
        h1 ^= key.length;
        h1 ^= h1 >>> 16;
        h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
        h1 ^= h1 >>> 13;
        h1 = (((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16)) & 0xffffffff;
        h1 ^= h1 >>> 16;
        res = h1 >>> 0;
        if (asHex) {
          return res.toString(16);
        } else {
          return res;
        }
      };


      /*
        	 * Simple JS templates.
        	 *
        	 * This is based on Underscore.template: http://underscorejs.org/#template
        	 *
        	 * Except there are added features that make it more lenient to errors.
        	 * - Prefix an escape or interpolation string with '@' to wrap it in a try/catch:
        	 *     <%- @possibleFailure() %>
        	 *
        	 * - If you are outputting simple variables, they are automatically wrapped with undefined checks
        	 *     <%- this.doesnt.exist %>
        	 *   The above wont result in an error, just an empty string. Pass settings.disableSafeVar to disable this.
        	 *
        	 * @param {String} text The template text
        	 * @param {Object} settings
        	 * @return Function
       */

      DeskPRO_Util_Strings.prototype.simpleTemplate = function(text, settings) {
        var STRINGS, e, fn, index, render, resolveVar, source;
        if (settings == null) {
          settings = {};
        }
        STRINGS = this;
        index = 0;
        source = "var __t, __p = '', __j=Array.prototype.join, __escape=Strings.escapeHtml, ";
        source += "print=function(){__p+=__j.call(arguments,'');};\n";
        source += "with(obj||{}){\n";
        source += "__p+='";
        resolveVar = function(varname) {
          var m, names, p, parts, testparts, _i, _len;
          if (settings.disableSafeVar) {
            return varname;
          }
          m = varname.match(/^\s*([a-zA-Z0-9\_\\$\\.]+)\s*$/);
          if (!m) {
            return varname;
          }
          testparts = [];
          names = [];
          parts = m[1].split('.');
          for (_i = 0, _len = parts.length; _i < _len; _i++) {
            p = parts[_i];
            names.push(p);
            testparts.push("typeof " + names.join('.') + " !== 'undefined' && " + names.join('.') + " !== null");
          }
          return "((" + testparts.join(' && ') + ") ? " + varname + " : '')";
        };
        text.replace(DeskPRO_Util_Strings.TPL_MATCHER, function(match, escape, interpolate, evaluate, offset) {
          source += text.slice(index, offset).replace(DeskPRO_Util_Strings.TPL_ESCAPER, function(m) {
            return '\\' + DeskPRO_Util_Strings.TPL_ESCAPES[m];
          });
          if (escape) {
            escape = STRINGS.trim(escape);
            if (escape[0] === '@') {
              source += "';\ntry { __p+= ((__t=(" + escape.substring(1) + "))==null?'':__escape(__t)); } catch (e) {}\n__p+='";
            } else {
              source += "'+\n((__t=(" + resolveVar(escape) + "))==null?'':__escape(__t))+\n'";
            }
          }
          if (interpolate) {
            interpolate = STRINGS.trim(interpolate);
            if (interpolate[0] === '@') {
              source += "';\ntry { __p+= ((__t=(" + interpolate.substring(1) + "))==null?'':__t+''); } catch (e) {}\n__p+='";
            } else {
              source += "'+\n((__t=(" + resolveVar(interpolate) + "))==null?'':__t+'')+\n'";
            }
          }
          if (evaluate) {
            source += "';\n" + evaluate + "\n__p+='";
          }
          index = offset + match.length;
          return match;
        });
        source += "';\n}\nreturn __p;\n";
        try {
          render = new Function('obj', 'Strings', source);
        } catch (_error) {
          e = _error;
          console.log("Error compiling source: " + source);
          e.source = source;
          throw e;
        }
        fn = function(data) {
          return render.call(this, data, window.STRINGS);
        };
        fn.source = source;
        return fn;
      };

      return DeskPRO_Util_Strings;

    })();
    window.STRINGS = new DeskPRO_Util_Strings();
    return new DeskPRO_Util_Strings();
  });

}).call(this);

//# sourceMappingURL=Strings.js.map
