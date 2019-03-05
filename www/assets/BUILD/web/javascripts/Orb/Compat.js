Orb.createNamespace('Orb.Compat.WebForms');

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

				// NOTE: Technically this should Object.defineProperty at
				//       the next index, as push can be affected by
				//       properties on Object.prototype and Array.prototype.
				//       But that method's new, and collisions should be
				//       rare, so use the more-compatible alternative.
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
			// NOTE: Absolute correctness would demand Object.defineProperty
			//       be used.  But this method is fairly new, and failure is
			//       possible only if Object.prototype or Array.prototype
			//       has a property |i| (very unlikely), so use a less-correct
			//       but more portable alternative.
			if (i in t)
				res[i] = fun.call(thisArg, t[i], i, t);
		}

		return res;
	};
}

if (!Date.prototype.toISOString) {
	Date.prototype.toISOString = function() {
		function pad(n) { return n < 10 ? '0' + n : n }
		return this.getUTCFullYear() + '-'
			+ pad(this.getUTCMonth() + 1) + '-'
			+ pad(this.getUTCDate()) + 'T'
			+ pad(this.getUTCHours()) + ':'
			+ pad(this.getUTCMinutes()) + ':'
			+ pad(this.getUTCSeconds()) + 'Z';
	};
}

if (!Array.isArray) {
	Array.isArray = function(arg) {
		return Object.prototype.toString.call(arg) === '[object Array]';
	};
}

if (!window.requestAnimationFrame) {
	window.requestAnimationFrame = function(fn) {
		return window.setTimeout(fn, 1);
	};
	window.cancelAnimationFrame = window.clearTimeout;
}

if (!window.requestIdleCallback) {
	window.requestIdleCallback = function(fn, opt) {
		var timeout = opt.timeout || 1;
		if (timeout < 1000) {
			timeout = 100;
		} else if (timeout > 10000) {
			timeout = 5000;
		} else {
			timeout = timeout * 0.5;
		}
		return window.setTimeout(fn, timeout);
	};
	window.cancelIdleCallback = window.clearTimeout;
}