Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketFlagged = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
	
	wrapper: null,

	initPage: function(el) {
		
		this.parent(el);
		
		this.wrapper = el;
		var self = this;
		
		$('.main-nav li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		$('.alt-nav li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		// Set up the poller
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-flagged-counts'}],
			'queue-flagged.counts',
			{recurring: true, minDelay: 60000, minDelayAfterOne: true}
		);
		(function() {
			DeskPRO_Window.getPoller().send();
		}).delay(500);
		
		// Automatically show the first flag
		var first = $('ul.flagged-list li:first', el);
		if (first.length) {
			DeskPRO_Window.runPageRouteFromElement(first);
		}
		
		// Set up listener
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.counts', this.updateCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.view-activated', this.highlightActiveFlag.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.view-deactivated', this.unhighlightActiveFlag.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.flag-changed', this.changeCountsForSwitch.bind(this));
	},
	
	updateCounts: function(counts) {
		
		$('.ticket-flag', this.wrapper).html('0');
		
		Object.each(counts, (function (count, flag) {
			this.updateCountFor(flag, count);
		}).bind(this));
	},
	
	updateCountFor: function(flag, count) {
		var count_str = count;
		if (count >= 1000) {
			count_str = '1000+';
		} else if (count < 0) {
			count = 0;
			count_str = '0';
		}

		var el = $('.ticket-flag-count-' + flag, this.wrapper).html(count);

		if (count == 0) {
			el.removeClass('new');
		} else {
			el.addClass('new');
		}
	},
	
	changeCountsForSwitch: function(info) {

		var old_flag_count = parseInt($('.ticket-flag-count-' + info.old_flag, this.wrapper).html());
		var new_flag_count = parseInt($('.ticket-flag-count-' + info.new_flag, this.wrapper).html());
		
		this.updateCountFor(info.old_flag, old_flag_count-1);
		this.updateCountFor(info.new_flag, new_flag_count+1);
	},
	
	highlightActiveFlag: function(flag) {
		$('.icon-flag-' + flag, this.wrapper).addClass('on');
	},
	unhighlightActiveFlag: function(flag) {
		$('.icon-flag-' + flag, this.wrapper).removeClass('on');
	}
});