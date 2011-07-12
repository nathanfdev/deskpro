Orb.createNamespace('DeskPRO.User');

/**
 * User window handler
 */
DeskPRO.User.Window = new Orb.Class({

	Extends: DeskPRO.BasicWindow,

	init: function() {
		this.PAGE = null;
	},

	initPage: function() {
		if (this.PAGE) {
			this.PAGE.initPage();
		}

		var elementHandlers = {};
		$('.with-handler[data-element-handler]').each(function() {
			var el = $(this);
			var className = el.data('element-handler');
			var classObj = Orb.getNamespacedObject(className);

			if (!classObj) {
				console.error("Unknown portal handler `%s` on element %o", className, this);
				return;
			}

			if (!el.attr('id')) {
				el.attr('id', Orb.getUniqueId('portal_'));
			}

			var obj = new classObj({ el: el });
			elementHandlers[el.attr('id')] = obj;
		});

		this.elementHandlers = elementHandlers;

		$('a.in-overlay').click(function(ev) {
			ev.preventDefault();
			
			var el = $(this);
			var url = el.attr('href');
			if (url.indexOf('?') !== -1) {
				url += '&_partial';
			} else {
				url += '?_partial';
			}

			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'ajax',
				contentAjax: {
					url: url
				},
				destroyOnClose: true
			});

			overlay.open();
		});
	},

	getHandler: function(id) {
		return this.handlers[id];
	},

	hasHandler: function(id) {
		return !!this.handlers[id];
	},

	setPageHandler: function(page) {
		this.PAGE = page;
	},

	getPageHandler: function() {
		return this.PAGE;
	}
});