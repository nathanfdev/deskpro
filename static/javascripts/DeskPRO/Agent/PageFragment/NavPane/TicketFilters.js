Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketFilters = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
	
	initPage: function(el) {
		$('li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		// Set up the poller
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-filter-counts'}],
			'filters.counts',
			{recurring: true}
		);
		
		// Set up listener
		DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts.bind(this));
		
		// Get them now, or very soon, so dont wait for normal polling interval
		(function() {
			DeskPRO_Window.getPoller().send();
		}).delay(500);
	},
	
	updateFilterCounts: function(counts) {
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
	}
});