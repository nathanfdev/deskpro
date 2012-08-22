Orb.createNamespace('DeskPRO.Report.ElementHandler.Builder');

DeskPRO.Report.ElementHandler.Builder.ReportList = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var el = this.el,
			offset = el.offset(),
			cookie = el.data('cookie'),
			cookieValue = cookie ? $.cookie(cookie) : false,
			widthDiff = el.outerWidth() - el.width(),
			minWidth = el.data('min') || 100,
			maxWidth = el.data('max') || 1000,
			dragger;

		if (cookieValue) {
			el.css('width', Math.min(maxWidth, Math.max(minWidth, cookieValue)) + 'px');
		}

		dragger = $('<div class="report-list-dragger" />').css({
			position: 'absolute',
			height: el.outerHeight() + 'px',
			left: (offset.left + el.outerWidth()) + 'px',
			top: offset.top + 'px'
		}).draggable({
			axis: 'x',
			containment: [offset.left + minWidth + widthDiff, 0, offset.left + maxWidth + widthDiff, 0],
			drag: function(e) {
				var width = Math.max(minWidth, dragger.offset().left - offset.left - widthDiff);
				el.css('width', width + 'px');
				if (cookie) {
					$.cookie(cookie, width, {expires: 7});
				}
			}
		}).appendTo(document.body);
	}
});
