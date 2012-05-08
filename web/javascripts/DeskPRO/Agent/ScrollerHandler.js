Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.ScrollerHandler = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(pageObject, element, options) {
		this.pageObject = pageObject;
		this.element = $(element);
		this.hasInit = false;
		var self = this;

		this.options = {
			'showEvent': false,
			'hideEvent': false
		};

		this.element.data('scroll_handler', this);
		this.element.addClass('with-scroll-handler');

		this.setOptions(options);

		if (pageObject && pageObject.addEvent && this.options.showEvent) {
			pageObject.addEvent(this.options.showEvent, function() {
				this.updateSize();
			}, this);
		} else {
			this._initScroll();
		}
	},

	_initScroll: function() {
		if (this.hasInit) return;
		this.hasInit = true;
		this.element.tinyscrollbar();
	},

	updateSize: function() {
		var self = this;
		window.setTimeout(function() {
			self._initScroll();
			self.element.tinyscrollbar_update();
			self.element.trigger('dp_resize');
		}, 250);
	}
});
