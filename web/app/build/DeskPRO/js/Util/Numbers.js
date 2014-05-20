(function() {
  define(function() {
    var DeskPRO_Util_Numbers;
    DeskPRO_Util_Numbers = (function() {
      function DeskPRO_Util_Numbers() {}


      /*
        	 * Get the value in <var>numbers</var> that is closest to <var>value</var>.
        	 *
        	 * @param {Number} value
        	 * @param {Array}  numbers
        	 * @return {Number}
       */

      DeskPRO_Util_Numbers.prototype.closest = function(value, numbers) {
        var closest, n, _i, _len;
        if (!this.isNumber(value)) {
          value = this.parseNumber(value);
        }
        closest = null;
        for (_i = 0, _len = numbers.length; _i < _len; _i++) {
          n = numbers[_i];
          if (!this.isNumber(n)) {
            n = this.parseNumber(n);
          }
          if (closest === null || Math.abs(n - value) < Math.abs(closest - value)) {
            closest = n;
          }
        }
        return closest;
      };


      /*
        	 * Takes a string <var>value</var> and returns an integer
        	 * or float. NaN's return as 0.
        	 *
        	 * @param {String} value
        	 * @return {Number}
       */

      DeskPRO_Util_Numbers.prototype.parseNumber = function(value) {
        var n;
        if (value && (value + "").indexOf('.') !== -1) {
          n = parseFloat(value);
        } else {
          n = parseInt(value);
        }
        if (!n || isNaN(n)) {
          n = 0;
        }
        return n;
      };


      /*
        	 * Check if <var>value</var> is a number type (int/float)
        	 *
        	 * @param {Integer/Float/mixed} value
        	 * @return {Boolean}
       */

      DeskPRO_Util_Numbers.prototype.isNumber = function(value) {
        return typeof value === 'number' && isFinite(value);
      };


      /*
        	 * Check if a value is a valid number-like value.
        	 * This is any number (int/float) or a string containing a number value.
        	 *
        	 * @param {Integer/Float/mixed} value
        	 * @return {Boolean}
       */

      DeskPRO_Util_Numbers.prototype.isNumeric = function(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
      };

      return DeskPRO_Util_Numbers;

    })();
    window.NUMBERS = new DeskPRO_Util_Numbers();
    return new DeskPRO_Util_Numbers();
  });

}).call(this);

//# sourceMappingURL=Numbers.js.map
