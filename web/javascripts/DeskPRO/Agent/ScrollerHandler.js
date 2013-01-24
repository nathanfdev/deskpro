Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.ScrollerHandler = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(pageObject, element, options) {
		var hasInit = false;

		$.extend(options, {
			'showEvent': false,
			'hideEvent': false
		});

		element = $(element);
		element.data('scroll_handler', this);
		element.addClass('with-scroll-handler');

		var onScrollTimer = false;

		function initScroll() {
			if (!element) return;
			if (hasInit) return;
			hasInit = true;

			element.tinyscrollbar();
			element.on('dp_scroll', function() {
				if (!onScrollTimer) {
					onScrollTimer = setTimeout(function() {
						onScrollTimer = false;
						if (element) {
							element.find('.select2-dropdown-open').select2("positionDropdown");
						}
					}, 25);
				}
			});
		}

		function updateSize() {
			if (!element) return;
			initScroll();
			if (element.tinyscrollbar_update) {
				element.tinyscrollbar_update();
			}
		}

		function restorePosition() {
			if (!element) return;
			if (hasInit && element) {
				element.trigger('restorescroll');
			}
		}

		function destroy() {
			if (!element) return;
			if (hasInit && element.tinyscrollbar_destroy) {
				element.tinyscrollbar_destroy();
			}
			element = null;
			options = null;
			pageObject = null;
		};

		if (pageObject && pageObject.addEvent && options.showEvent) {
			pageObject.addEvent(options.showEvent, updateSize);
		} else {
			initScroll();
		}

		this.updateSize = updateSize;
		this.restorePosition = restorePosition;
		this.destroy = destroy;
	}
});
