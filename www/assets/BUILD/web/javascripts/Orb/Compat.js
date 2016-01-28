Orb.createNamespace('Orb.Compat.WebForms');

Orb.Compat.WebForms.isPlaceholderSupported = function() {
	this.isSupported = null;

	if (this.isSupported === null) {
		this.isSupported = ('placeholder' in document.createElement('input'));
	}

	return this.isSupported;
};

Orb.Compat.WebForms.placeholder = function(input) {

	if (!input) return null;

	input_col = $(input);
	if (!input_col.length) return null;

	input_col.each(function() {

		var input = $(this);

		// Check if its already supported
		if (input.placeholder && this.isPlaceholderSupported()) return;

		// Get the palceholder and check that its actually a value
		var placeholder = input.attr('placeholder');
		if (!placeholder || !placeholder.length) return;

		// Make sure we havent already run the placeholder compat
		if (input.is('.has-placeholder')) return;

		input.addClass('has-placeholder');

		// See if we should enable the placeholder now
		if (input.val() === '' || input.val() == placeholder) {
			input.val(placeholder);
			input.addClass('placeholder-visible');
		}

		input.focus(function() {
			if (input.is('.placeholder-visible')) {
				input.val('');
				input.removeClass('placeholder-visible');
			}
		});

		input.blur(function() {
			if (input.val() === '') {
				input.addClass('placeholder-visible');
				input.val(placeholder);
			} else {
				input.removeClass('placeholder-visible');
			}
		});

		if (input.get(0) && input.get(0).form) {
			$(input.get(0).form).submit(function() {
				if (input.is('.placeholder-visible')) {
					input.val('');
				}
			});
		}
	});
};

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