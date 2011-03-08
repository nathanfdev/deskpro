Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketFilters = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,

	wrapper: null,

	cancelClickActivateFilter: false,
	initPage: function(el) {

		this.parent(el);

		this.wrapper = el;
		var self = this;

		$('.main-nav li', el).click(function() {
			if (self.cancelClickActivateFilter) {
				self.cancelClickActivateFilter = false;
				return;
			}
			DeskPRO_Window.runPageRouteFromElement(this);
		});

		$('.alt-nav li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});

		// Set up the poller
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-filter-counts'}],
			'filters.counts',
			{recurring: true, minDelay: 15000, minDelayAfterOne: true}
		);

		// Get them now, or very soon, so dont wait for normal polling interval
		(function() {
			DeskPRO_Window.getPoller().send();
		}).delay(500);

		// Automatically run the first filter
		var first_filter = $('ul.filter-list li:first', el);
		if (first_filter.length) {
			DeskPRO_Window.runPageRouteFromElement(first_filter);
		}

		// Set up listener
		DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updatefilterCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('filter.view-activated', this.highlightActiveFilter.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('filter.view-deactivated', this.unhighlightActiveFilter.bind(this));

		this._initFilterReorder();
	},

	toggleAltNavTo: function(el) {
		$('.alt-nav li.active', this.wrapper).removeClass('active');
		el.addClass('active');
	},

	updatefilterCounts: function(counts) {
		Object.each(counts, function (count, filter_id) {
			var count_str = count;
			if (count >= 1000) count_str = '1000+';

			var el = $('.ticket-filter-count-' + filter_id).html(count);

			if (count == 0) {
				el.removeClass('new');
			} else {
				el.addClass('new');
			}
		});
	},

	highlightActiveFilter: function(filter_id) {
		$('.filter-' + filter_id, this.wrapper).addClass('on');
	},
	unhighlightActiveFilter: function(filter_id) {
		$('.filter-' + filter_id, this.wrapper).removeClass('on');
	},


	//#################################################################
	//# Drag+drop reorder
	//#################################################################

	_initFilterReorder: function() {
		var self = this;
		$('ul.filter-list', this.wrapper).sortable({
			'axis': 'y',
			'containment': this.wrapper,
			'distance': 8,
			'deactivate': function() {
				self.cancelClickActivateFilter = true;
			},
			'update': function() {
				self.saveFilterOrder();
			}
		});
	},

	saveFilterOrder: function() {
		var data = [];

		$('.main-nav li', this.wrapper).each(function() {
			var id = $(this).data('filter-id');
			if (id) {
				data.push({ name: 'prefs[agent.ui.ticket-filters-order][]', value: id });
			}
		});

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/misc/ajax-save-prefs',
			data: data
		});
	}
});