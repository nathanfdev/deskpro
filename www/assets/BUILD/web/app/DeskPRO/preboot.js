(function() {
  if (!Array.prototype.forEach)
  {
    Array.prototype.forEach = function(fun /*, thisArg */)
    {
      "use strict";

      if (this === void 0 || this === null)
        throw new TypeError();

      var t = Object(this);
      var len = t.length >>> 0;
      if (typeof fun !== "function")
        throw new TypeError();

      var thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      for (var i = 0; i < len; i++)
      {
        if (i in t)
          fun.call(thisArg, t[i], i, t);
      }
    };
  }

  if (!Array.prototype.filter)
  {
    Array.prototype.filter = function(fun /*, thisArg */)
    {
      "use strict";

      if (this === void 0 || this === null)
        throw new TypeError();

      var t = Object(this);
      var len = t.length >>> 0;
      if (typeof fun != "function")
        throw new TypeError();

      var res = [];
      var thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      for (var i = 0; i < len; i++)
      {
        if (i in t)
        {
          var val = t[i];
          if (fun.call(thisArg, val, i, t))
            res.push(val);
        }
      }

      return res;
    };
  }

  if (!Array.prototype.map)
  {
    Array.prototype.map = function(fun /*, thisArg */)
    {
      "use strict";

      if (this === void 0 || this === null)
        throw new TypeError();

      var t = Object(this);
      var len = t.length >>> 0;
      if (typeof fun !== "function")
        throw new TypeError();

      var res = new Array(len);
      var thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      for (var i = 0; i < len; i++)
      {
        if (i in t)
          res[i] = fun.call(thisArg, t[i], i, t);
      }

      return res;
    };
  }

  if (!Array.prototype.findIndex)
  {
    Array.prototype.findIndex = function(fun /*, thisArg */)
    {
      "use strict";

      if (this === void 0 || this === null)
        throw new TypeError();

      var t = Object(this);
      var len = t.length >>> 0;
      if (typeof fun !== "function")
        throw new TypeError();
      var thisArg = arguments.length >= 2 ? arguments[1] : void 0;

      for (var i = 0; i < len; i++)
      {
        if (fun.call(thisArg, t[i], i, t)) {
          return i;
        }
      }

      return undefined;
    };
  }

  if (!Array.prototype.find)
  {
    Array.prototype.find = function(fun /*, thisArg */)
    {
      "use strict";
      var thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      var idx;

      if (arguments.length >= 2) {
        idx = this.findIndex(fun, arguments[1]);
      } else {
        idx = this.findIndex(fun);
      }

      if (typeof idx != 'undefined') {
        return this[idx];
      }

      return undefined;
    };
  }
})();