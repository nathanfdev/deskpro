(function () {
  if (!Array.prototype.forEach)  {
    Array.prototype.forEach = function (fun /* , thisArg */)    {
      'use strict';

      if (this === void 0 || this === null)        { throw new TypeError(); }

      const t = Object(this);
      const len = t.length >>> 0;
      if (typeof fun !== 'function')        { throw new TypeError(); }

      const thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      for (let i = 0; i < len; i++)      {
        if (i in t)          { fun.call(thisArg, t[i], i, t); }
      }
    };
  }

  if (!Array.prototype.filter)  {
    Array.prototype.filter = function (fun /* , thisArg */)    {
      'use strict';

      if (this === void 0 || this === null)        { throw new TypeError(); }

      const t = Object(this);
      const len = t.length >>> 0;
      if (typeof fun !== 'function')        { throw new TypeError(); }

      const res = [];
      const thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      for (let i = 0; i < len; i++)      {
        if (i in t)        {
          const val = t[i];
          if (fun.call(thisArg, val, i, t))            { res.push(val); }
        }
      }

      return res;
    };
  }

  if (!Array.prototype.map)  {
    Array.prototype.map = function (fun /* , thisArg */)    {
      'use strict';

      if (this === void 0 || this === null)        { throw new TypeError(); }

      const t = Object(this);
      const len = t.length >>> 0;
      if (typeof fun !== 'function')        { throw new TypeError(); }

      const res = new Array(len);
      const thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      for (let i = 0; i < len; i++)      {
        if (i in t)          { res[i] = fun.call(thisArg, t[i], i, t); }
      }

      return res;
    };
  }

  if (!Array.prototype.findIndex)  {
    Array.prototype.findIndex = function (fun /* , thisArg */)    {
      'use strict';

      if (this === void 0 || this === null)        { throw new TypeError(); }

      const t = Object(this);
      const len = t.length >>> 0;
      if (typeof fun !== 'function')        { throw new TypeError(); }
      const thisArg = arguments.length >= 2 ? arguments[1] : void 0;

      for (let i = 0; i < len; i++)      {
        if (fun.call(thisArg, t[i], i, t)) {
          return i;
        }
      }

      return undefined;
    };
  }

  if (!Array.prototype.find)  {
    Array.prototype.find = function (fun /* , thisArg */)    {
      'use strict';

      const thisArg = arguments.length >= 2 ? arguments[1] : void 0;
      let idx;

      if (arguments.length >= 2) {
        idx = this.findIndex(fun, arguments[1]);
      } else {
        idx = this.findIndex(fun);
      }

      if (typeof idx !== 'undefined') {
        return this[idx];
      }

      return undefined;
    };
  }
}());
