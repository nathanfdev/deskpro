Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketFilters = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
	
	wrapper: null,
	
	cancelClickActivateQueue: false,
	initPage: function(el) {
		
		this.parent(el);
		
		this.wrapper = el;
		var self = this;
		
		$('.main-nav li', el).click(function() {
			if (self.cancelClickActivateQueue) {
				self.cancelClickActivateQueue = false;
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
		DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue.view-activated', this.highlightActiveQueue.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue.view-deactivated', this.unhighlightActiveQueue.bind(this));
		
		this._initQueueReorder();
	},
	
	toggleAltNavTo: function(el) {
		$('.alt-nav li.active', this.wrapper).removeClass('active');
		el.addClass('active');
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
	},
	
	highlightActiveQueue: function(queue_id) {
		$('.queue-' + queue_id, this.wrapper).addClass('on');
	},
	unhighlightActiveQueue: function(queue_id) {
		$('.queue-' + queue_id, this.wrapper).removeClass('on');
	},
	
	
	//#################################################################
	//# Drag+drop reorder
	//#################################################################
	
	_initQueueReorder: function() {
		var self = this;
		$('ul.filter-list', this.wrapper).sortable({
			'axis': 'y',
			'containment': this.wrapper,
			'distance': 8,
			'deactivate': function() {
				self.cancelClickActivateQueue = true;
			},
			'update': function() {
				self.saveQueueOrder();
			}
		});
	},
	
	saveQueueOrder: function() {
		var data = [];
		
		$('.main-nav li', this.wrapper).each(function() {
			var id = $(this).data('queue-id');
			if (id) {
				data.push({ name: 'prefs[agent.ui.ticket-queues-order][]', value: id });
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