Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.ScrollerHandler = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(pageObject, $element, options) {
		var scroller;
		var resetLastX = null;
		var resetLastY = null;
		var resetTimeout = null;

		$.extend(options, {
			'showEvent': false,
			'hideEvent': false
		});

		$element = $($element);
		$element.data('scroll_handler', this);
		$element.addClass('with-scroll-handler');

		$element.on('goscrolltop', function() {
			initScroll();
			scroller.scrollTo(0,0);
		});

		$element.on('scrollupdate', function() {
			updateSize();
		});

		$element.on('goscrollbottom', function() {
			scroller.scrollBy(0, 1000000);
		});

		$element.on('goscrollbottom_stick', function() {
			scroller.scrollBy(0, 1000000);
		});

		function initScroll() {
			if (!$element) return;
			if (scroller) return;

			scroller = new IScroll($element.get(0), {
				scrollbars: true,
				mouseWheel: true,
				interactiveScrollbars: true,
				bounce: false,
				resizePolling: 100000000,
				useTransition: false,
				useTransform: false,
				preventDefault: false
			});

			scroller.on('refresh', function() {
				if (resetLastX !== null && resetLastY !== null) {
					scroller.x = resetLastX;
					scroller.y = resetLastY;
					resetLastX = resetLastY = null;
				}
			});
		}

		function updateSize() {
			if (!$element || !scroller) return;

			var currentWrapperH  = scroller.wrapperHeight;
			var currentScrollerH = scroller.scrollerHeight;

			var rf = scroller.wrapper.offsetHeight;
			var newWrapperH  = scroller.wrapper.clientHeight;
			var newScrollerH = scroller.scroller.offsetHeight;

			if (
				(currentWrapperH != newWrapperH)
				|| (currentScrollerH != newScrollerH)
			) {
				if (resetTimeout) {
					window.clearTimeout(resetTimeout);
				}

				resetLastX = 0;
				resetLastY = parseInt($(scroller.scroller).css('top'));

				resetTimeout = window.setTimeout(function() {
					scroller.refresh();
				}, 20);
			}
		}

		function restorePosition() {
			return;
		}

		function destroy() {
			if (!$element) return;
			if (scroller && scroller.destroy) {
				scroller.destroy();
			}

			if (resetTimeout) {
				window.clearTimeout(resetTimeout);
			}

			$element = null;
			options = null;
			pageObject = null;
		};

		if (pageObject && pageObject.addEvent && options.showEvent) {
			pageObject.addEvent(options.showEvent, updateSize);
		} else {
			initScroll();
		}

		function scrollToElement(el) {
			el = $(el);
			initScroll();
			if (scroller) {
				scroller.scrollToElement(el.get(0));
			}
		}

		this.updateSize = updateSize;
		this.restorePosition = restorePosition;
		this.destroy = destroy;
		this.scrollToElement = scrollToElement;
		this.isInitialized = function() { return !!scroller; };
		this.getElement = function() { return $element; }
	}
});

DeskPRO.Agent.ScrollerHandler.attachHandler = function(pageObject, $element, options) {
	if ($element.hasClass('with-scroll-handler')) {
		return $element.data('scroll_handler');
	}

	var obj = new DeskPRO.Agent.ScrollerHandler(pageObject, $element, options);
	return obj;
};