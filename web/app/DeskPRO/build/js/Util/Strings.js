(function() {
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

      /*
        	# Generates a random string.
        	#
        	# @param {Integer} len     How long the generated string should be
        	# @param {String}  chars   A string of characters to choose form, or the name of a preset
        	# @return {String}
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
        	# Removes leading and trailing whitespace
        	#
        	# @param {String} string
        	# @return {String}
      */


      DeskPRO_Util_Strings.prototype.trim = function(string) {
        if (string.trim != null) {
          return string.trim();
        }
        return string.replace(/^\s+|\s+$/g, '');
      };

      /*
        	# Removes leading whitespace
      
        	# @param {String} string
        	# @return {String}
      */


      DeskPRO_Util_Strings.prototype.trimLeft = function(string) {
        if (string.trimLeft != null) {
          return string.trimLeft();
        }
        return string.replace(/^\s+/, '');
      };

      /*
        	# Removes trailing whitespace
        	#
        	# @param {String} string
        	# @return {String}
      */


      DeskPRO_Util_Strings.prototype.trimRight = function(string) {
        if (string.trimRight != null) {
          return string.trimRight();
        }
        return string.replace(/\s+$/, '');
      };

      /*
        	# Given a string with words separated by dashes, underscores or spaces, convert it into
        	# camel case. For example "my-string" and "my_string" becomes myString
        	#
        	# @param {String} string
        	# @return {String}
      */


      DeskPRO_Util_Strings.prototype.toCamelCase = function(string) {
        return string.toLowerCase().replace(/[\-_ ]{1}([a-zA-Z])/g, function(match, group1) {
          return group1.toUpperCase();
        });
      };

      /*
        	# Uppercase the first letter of a string
        	#
        	# @param {String} string
        	# @return {String}
      */


      DeskPRO_Util_Strings.prototype.ucFirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      };

      return DeskPRO_Util_Strings;

    })();
    return new DeskPRO_Util_Strings();
  });

}).call(this);

/*
//@ sourceMappingURL=Strings.js.map
*/