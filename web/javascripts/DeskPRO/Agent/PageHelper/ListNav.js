Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.ListNav = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page) {
		this.page = page;
		this.itemSelector    = 'article.row-item';
		this.activeClass     = 'selection-on';
		this.scrollContainer = this.page.wrapper.find('.scroll-content').first();
		this.scrollView      = this.page.wrapper.find('.scroll-viewport').first();
	},

	getCurrentSelection: function() {
		var el = this.page.wrapper.find(this.itemSelector).filter('.' + this.activeClass);
		if (el[0]) {
			return el;
		}

		return null;
	},

	scrollIntoView: function(row) {
		if (!row || !row.position()) {
			return;
		}

		if (this.scrollContainer.height() <= this.scrollView.height()) {
			return;
		}

		var viewTop = Math.abs(parseInt(this.scrollContainer.css('top')));
		var viewBtm = viewTop + this.scrollContainer.parent().height();

		var elTop = row.position().top;
		var elBtm = elTop + row.height() + row.height() + 20;

		if (viewTop < elTop && viewBtm > elBtm) {
			// Already visble
			return;
		}

		var scrollTo = elBtm - this.scrollContainer.parent().height();
		if (scrollTo < 0) {
			scrollTo = 0;
		}

		var scroll = this.page.wrapper.find('.with-scrollbar').first();
		scroll.trigger('goscrollto', [scrollTo]);
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
		this.scrollIntoView(next);

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

		next.addClass(this.activeClass)
		this.scrollIntoView(next);

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
