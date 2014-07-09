// Converts click events to touch events
if (window.jQuery && ('ontouchstart' in window || 'msmaxtouchpoints' in window.navigator)) {
	var originalOnMethod  = jQuery.fn.on,
		originalOffMethod = jQuery.fn.off;

	var replaceEventName = function (event) {
		if (event.slice(0, 5) == 'click') {
			// We need to attach events to both click and touch
			// Because the user might have both a touch *and* mouse
			return event.replace('click', 'click touchend');
		}
		return event;
	}

	jQuery.fn.click = function() {
		return jQuery.fn.on.apply(this, ['click', arguments[0]]);
	};

	// Change event type and re-apply .on() method
	jQuery.fn.on = function() {
		var fnKey = 1, oldFn, hasClicked = false;

		// on(eventName, fn)
		// or on(eventName, selector, fn)
		// So we need to detect those
		if (typeof arguments[2] == 'function') {
			fnKey = 2;
		}
		oldFn = arguments[fnKey];
		if (!oldFn) {
			return this;
		}
		arguments[0] = replaceEventName(arguments[0]);

		// We need to override handling of touch events a bit
		// - We want to cancel clicking when the user has scrolled by dragging
		// so the original 'down' event doesnt fire after touch has ended
		// - We want to cancel double-clicking that can sometimes happen when click and touch
		// both fire
		arguments[fnKey] = function() {
			if (window.DP_SCROLL_CANCEL_TOUCH) {
				window.DP_SCROLL_CANCEL_TOUCH = false;
				hasClicked = false;
				return;
			}
			if (hasClicked) {
				hasClicked = false;
				return;
			}
			hasClicked = true;
			window.setTimeout(function() {
				hasClicked = false;
			}, 80);
			return oldFn.apply(this, arguments);
		};

		originalOnMethod.apply(this, arguments);
		return this;
	};

	// Change event type and re-apply .on() method
	jQuery.fn.one = function() {
		var fnKey = 1, oldFn, hasClicked = false;

		if (typeof arguments[2] == 'function') {
			fnKey = 2;
		}
		oldFn = arguments[fnKey];
		if (!oldFn) {
			return this;
		}
		arguments[0] = replaceEventName(arguments[0]);

		arguments[fnKey] = function() {
			if (window.DP_SCROLL_CANCEL_TOUCH) {
				window.DP_SCROLL_CANCEL_TOUCH = false;
				hasClicked = false;
				return;
			}
			if (hasClicked) {
				hasClicked = false;
				return;
			}
			hasClicked = true;
			window.setTimeout(function() {
				hasClicked = false;
			}, 80);
			return oldFn.apply(this, arguments);
		};

		originalOnMethod.apply(this, arguments);
		return this;
	};

	// Change event type and re-apply .off() method
	jQuery.fn.off = function() {
		// arguments[0] is the event name
		arguments[0] = replaceEventName(arguments[0]);

		originalOffMethod.apply(this, arguments);
		return this;
	};
}