// Converts click events to touch events
if (window.jQuery && ('ontouchstart' in window || 'msmaxtouchpoints' in window.navigator)) {
	var originalOnMethod  = jQuery.fn.on,
		originalOffMethod = jQuery.fn.off;

	var replaceEventName = function (event) {
		if (event.slice(0, 5) == 'click') {
			return event.replace('click', 'touchend');
		}
		return event;
	}

	// Change event type and re-apply .on() method
	jQuery.fn.on = function() {
		var oldFn = arguments[1];
		arguments[0] = replaceEventName(arguments[0]);
		arguments[1] = function() {
			if (window.DP_SCROLL_CANCEL_TOUCH) {
				window.DP_SCROLL_CANCEL_TOUCH = false;
				return;
			}
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