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
        return function() {
          var args, later, res, self, time, timeout;
          self = this;
          args = arguments;
          time = (new Date()).getTime();
          later = function() {
            var last, res, timeout;
            last = (new Date()).getTime() - time;
            if (last < wait) {
              return timeout = setTimeout(later, wait - last);
            } else {
              timeout = null;
              if (!immediate) {
                res = fn.apply(self, args);
                self = null;
                return args = null;
              }
            }
          };
          if (!timeout) {
            timeout = setTimeout(later, wait);
          }
          if (immediate && !timeout) {
            res = fn.apply(self, args);
            self = null;
            args = null;
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
