Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketFlagged = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
	
	wrapper: null,

	initPage: function(el) {
		
		this.wrapper = el;
		var self = this;
		
		$('.main-nav li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		$('.alt-nav li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		// Automatically show the first flag
		var first = $('ul.flagged-list li:first', el);
		if (first.length) {
			DeskPRO_Window.runPageRouteFromElement(first);
		}
		
		// Set up listener
		//DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.view-activated', this.highlightActiveFlag.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.view-deactivated', this.unhighlightActiveFlag.bind(this));
	},
	
	updateFlagCounts: function(counts) {
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
	
	highlightActiveFlag: function(flag) {
		$('.icon-flag-' + flag, this.wrapper).addClass('on');
	},
	unhighlightActiveFlag: function(flag) {
		$('.icon-flag-' + flag, this.wrapper).removeClass('on');
	}
});