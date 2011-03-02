Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.Tickets = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	buttonClass: 'tickets-nav',

	init: function () {

		window.MAINMENU_TICKET = this;

		// Sends ajax to fetch initial data
		this._initInitialData();

	},

	_initAfterInitialData: function() {
		if (this._initerCount > 0) return; //notyet

		// Get them now, or very soon, so dont wait for normal polling interval
		(function() {
			DeskPRO_Window.getPoller().send();
		}).delay(500);
	},

	// we use a counter to make sure initAfterInitialData is only fired once, after all panes are loaded
	_initerCount: 0,
	_initInitialData: function() {
		this._initerCount++;
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/queues-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('ol#filters_list').html(html);
				this._initerCount--;
				this._initAfterInitialData();
			}
		});

		this._initerCount++;
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/flagged-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('ol#flagged_list').html(html);
				this._initerCount--;
				this._initAfterInitialData();
			}
		});

		this._initerCount++;
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/labels-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('ol#labels_cloud_list').html(html);
				this._initerCount--;
				this._initAfterInitialData();
			}
		});
	},



	//#########################################################################
	// Filter functionality
	//#########################################################################

	_initFilters: function() {
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-queue-counts'}],
			'queues.counts',
			{recurring: true, minDelay: 15000, minDelayAfterOne: true}
		);

		DeskPRO_Window.getMessageBroker().addMessageListener('queues.counts', this.updateQueueCounts.bind(this));
	},

	/**
	 * When the poller comes back with new counts, we should update the UI with them.
	 *
	 * @param {Object} counts
	 */
	updateQueueCounts: function(counts) {
		var badgeCount = 0;

		Object.each(counts, function (count, queue_id) {
			var count_str = count;
			queue_id = parseInt(queue_id);
			if (count >= 1000) count_str = '1000+';

			var system_name = DeskPRO_Window.getData('systemQueues')[queue_id];
			if (system_name) {
				badgeCount += count;
				var el = $('#filter_' + system_name + '_count').html(count_str);
			} else {
				var el = $('#filter_' + queue_id + '_count').html(count_str);
			}
		});

		this.updateBadge(badgeCount);
	},

	//#########################################################################
	// Flags functionality
	//#########################################################################

	 _initFlagged: function() {

		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.counts', this.updateCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.view-activated', this.highlightActiveFlag.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.view-deactivated', this.unhighlightActiveFlag.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('queue-flagged.flag-changed', this.changeCountsForSwitch.bind(this));

	 }

});