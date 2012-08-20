Orb.createNamespace('DeskPRO.Report.ElementHandler.Builder');

DeskPRO.Report.ElementHandler.Builder.ListCollapse = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var el = this.el,
			statusEl = el.find('span:first');
			targetEl = el.data('target') ? $(el.data('target')) : el.next();

		var updateStatusFunc = function() {
			if (targetEl.hasClass('collapsed')) {
				statusEl.text('+');
			} else {
				statusEl.text('-');
			}
		};

		el.click(function() {
			targetEl.toggleClass('collapsed');
			updateStatusFunc();
		});
		updateStatusFunc();
	}
});
