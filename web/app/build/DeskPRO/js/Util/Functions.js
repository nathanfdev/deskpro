(function() {
  define(function() {
    var DeskPRO_Util_Functions;
    DeskPRO_Util_Functions = (function() {
      function DeskPRO_Util_Functions() {}


      /*
        	 * Returns a function that will be called wait ms after the last time it was
        	 * invoked. E.g., if it was called 3 times in a row, it wouldnt actually be invoked
      		 * 3 times because it happened before wait time had passed.
        	 *
        	 * @param {Function} fn
        	 * @param {Integer} wait
        	 * @param {bool} immediate
       */

      DeskPRO_Util_Functions.prototype.debounce = function(fn, wait, immediate) {
        var res, timeout;
        timeout = null;
        res = null;
        return function() {
          var args, callNow, later, self;
          self = this;
          args = arguments;
          later = function() {
            timeout = null;
            if (!immediate) {
              return res = fn.apply(self, args);
            }
          };
          callNow = immediate && !timeout;
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
          if (callNow) {
            res = fn.apply(self, args);
          }
          return res;
        };
      };

      return DeskPRO_Util_Functions;

    })();
    return new DeskPRO_Util_Functions();
  });

}).call(this);

//# sourceMappingURL=Functions.js.map
