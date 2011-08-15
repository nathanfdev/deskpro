Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Ticket');

DeskPRO.Agent.PageFragment.Page.Ticket.TicketChecker = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		this.options = {
			interval: 8000,
			autoAfterPauseTime: 8000,
			lastMessageId: 0,
			lastLogId: 0,
			autostart: true
		};

		this.setOptions(options);

		this.page = page;
		this.paused = false;
		this.lastDate = null;
		this.timeout = null;
		this.activeAjax = null;

		this.lastMessageId = 0;
		this.lastLogId = 0;

		if (this.options.lastMessageId) {
			this.lastMessageId = this.options.lastMessageId;
		}
		if (this.options.lastLogId) {
			this.lastLogId = this.options.lastLogId;
		}

		this.sendData = {};

		if (this.autostart) {
			this.startTimer();
		}
	},

	pause: function(abort_ajax) {
		this.paused = true;
		if (this.timeout) {
			window.clearTimeout(this.timeout);
			this.timeout = null;
		}

		if (abort_ajax && this.activeAjax) {
			this.activeAjax.abort();
			this.activeAjax = null;
		}
	},

	unpause: function() {
		this.paused = false;

		if (this.lastDate) {
			var now = new Date();
			if (now.getTime() - this.lastDate.getTime()) {
				this.runCheck();
			}
		}

		this.startTimer();
	},

	isPaused: function() {
		return this.paused;
	},

	startTimer: function() {
		if (this.timeout) {
			window.clearTimeout(this.timeout);
		}

		this.timeout = window.setTimeout(this.runCheck.bind(this), this.options.interval);
	},

	setSendData: function(k, v) {
		this.sendData[k] = v;
	},

	getLastMessageId: function() {
		return this.lastMessageId;
	},

	getLastLogId: function() {
		return this.lastLogId;
	},

	runCheck: function() {
		if (this.timeout) {
			window.clearTimeout(this.timeout);
		}

		if (this.activeAjax) {
			return;
		}

		var data = $.extend({}, this.sendData);
		data.last_message_id = this.getLastMessageId();
		data.last_log_id = this.getLastLogId();
		
		this.fireEvent('sendData', [data]);

		this.activeAjax = $.ajax({
			url: this.options.checkUrl,
			type: 'GET',
			context: this,
			data: data,
			dataType: 'json',
			complete: function() {
				this.lastDate = new Date();
				this.startTimer();
				this.activeAjax = null;

				this.fireEvent('checkComplete');
			},
			success: function(data) {
				if (data.last_message_id && data.last_message_id > this.lastMessageId) {
					this.lastMessageId = data.last_message_id;
				}
				if (data.last_log_id && data.last_log_id > this.lastLogId) {
					this.lastLogId = data.last_log_id;
				}

				this.fireEvent('sendSuccess', [data]);
			}
		})
	},

	destroy: function() {
		if (this.timeout) {
			window.clearTimeout(this.timeout);
		}

		if (this.activeAjax) {
			this.activeAjax.abort();
			this.activeAjax = null;
		}
	}
});