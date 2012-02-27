Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.ListNav = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page) {
		this.page = page;
		this.itemSelector = 'article.row-item';
		this.activeClass = 'selection-on';
	},

	getCurrentSelection: function() {
		var el = this.page.wrapper.find(this.itemSelector).filter('.' + this.activeClass);
		if (el[0]) {
			return el;
		}

		return null;
	},

	down: function() {
		var current = this.getCurrentSelection();
		var next;
		if (current) {
			next = current.next(this.itemSelector);
			if (!next) {
				next = current;
			}
			current.removeClass(this.activeClass);
		} else {
			next = this.page.wrapper.find(this.itemSelector).first();
		}

		next.addClass(this.activeClass);

		return next;
	},

	up: function() {
		var current = this.getCurrentSelection();
		var next;
		if (current) {
			next = current.prev(this.itemSelector);
			if (!next) {
				next = current;
			}
			current.removeClass(this.activeClass);
		} else {
			next = this.page.wrapper.find(this.itemSelector).first();
		}

		next.addClass(this.activeClass);

		return next;
	},

	enter: function() {
		var current = this.getCurrentSelection();
		if (current) {
			DeskPRO_Window.runPageRouteFromElement(current);
		}
	},

	check: function() {
		var current = this.getCurrentSelection();
		if (current) {
			var check = current.find('input.item-select');
			if (check.prop('checked')) {
				check.prop('checked', false);
			} else {
				check.prop('checked', true);
			}
		}
	}
});
