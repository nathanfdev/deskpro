Orb.createNamespace('DeskPRO.User.Page');

/**
 * NewTicket functionality
 */
DeskPRO.User.Page.Portal = new Orb.Class({

	Extends: DeskPRO.User.Page.Abstract,

	initPage: function() {

		var handlers = {};

		$('[data-portal-handler]').each(function() {
			var el = $(this);
			var className = el.data('portal-handler');
			var classObj = Orb.getNamespacedObject(className);

			if (!classObj) {
				console.error("Unknown portal handler `%s` on element %o", className, this);
				return;
			}

			if (!el.attr('id')) {
				el.attr('id', Orb.getUniqueId('portal_'));
			}

			var obj = new classObj({ el: el });
			handlers[el.attr('id')] = obj;
		});

		this.handlers = handlers;
	},

	getHandler: function(id) {
		return this.handlers[id];
	},

	hasHandler: function(id) {
		return !!this.handlers[id];
	}
});