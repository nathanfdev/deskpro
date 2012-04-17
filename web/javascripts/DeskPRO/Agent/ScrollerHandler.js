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

		if (pageObject.addEvent) {
			pageObject.addEvent(this.options.showEvent, function() {
				this.updateSize();
			}, this);
		}

		var tiny = element.tinyscrollbar();
		element.data('scroll_handler', this);
		element.addClass('with-scroll-handler');
	},

	updateSize: function() {
		var el = this.element;
		window.setTimeout(function() {
			el.tinyscrollbar_update();
		}, 250);
	}
});
