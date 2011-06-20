Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.ScrollerHandler = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(pageObject, element, options) {
		this.pageObject = pageObject;
		this.element = element = $(element);

		this.options = {
			'showEvent': 'activate',
			'hideEvent': 'deactivate'
		};

		this.setOptions(options);

		element.tinyscrollbar();
		this.initUpdateTimer();

		// If pageObject supports Events, then we'll attach
		// activate/deactivate on timers. Just assume pageObject
		// uses those events (ie page fragments do)
		if (pageObject.addEvent) {
			var self = this;
			pageObject.addEvent(this.options.showEvent, function() {
				self.initUpdateTimer();
			});
			pageObject.addEvent(this.options.hideEvent, function() {
				self.removeResizeTimer();
			});
		}
	},

	initUpdateTimer: function() {
		var element = this.element;
		if (element.data('resize-special-event')) return;

		var viewport = $('div.scroll-viewport:first', element);
		viewport.resize(function() {
			// When size changes within the pane, need to re-size the scroll
			element.tinyscrollbar_update();
		});
	},

	removeResizeTimer: function() {
		this.element.unbind('resize');
	}
});