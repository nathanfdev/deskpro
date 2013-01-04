Orb.createNamespace('DeskPRO.MessageChanneler');

/**
 * The AJAX channeler uses AJAX polling to fetch new messages from the server.
 */
DeskPRO.MessageChanneler.AjaxChanneler = new Orb.Class({
	Extends: DeskPRO.MessageChanneler.AbstractChanneler,

	_init: function() {

		this._add_subs = [];
		this._add_subs_timeout = null;
		this._del_subs = [];
		this._del_subs_timeout = null;

		this.count = 0;

		this.lastMessageId = -1;
		this.poller = new DeskPRO.AjaxPoller.Poller({
			ajaxUrl: this.options.ajaxMessagesUrl,
			interval: this.options.interval,
			ajaxType: 'GET'
		});

		this.poller.addData((function () {
			if (this.lastMessageId === null) return null;
			return { 'since': this.lastMessageId };
		}).bind(this), 'since', { recurring: true });

		this.poller.addData((function () {
			return { 'count': ++this.count };
		}).bind(this), 'since', { recurring: true });

		this.poller.addData({is_initial_poll:1}, 'is_initial_poll');

		this.poller.addEvent('ajaxSuccess', this.handleMessageAjax.bind(this));

		if (this.options.lastMessageId) {
			this.lastMessageId = this.options.lastMessageId;
		}
	},

	handleMessageAjax: function(data) {
		if (!data) {
			console.debug("Data is false");
			return;
		}

		var ins_order, i, x, d, messages;
		var ordered = {};
		var orders = [];

		if (data.messages && data.messages.length) {
			for (x = 0; x < data.messages.length; x++) {
				d = data.messages[x];
				if (d[0] && (parseInt(d[0], 10) <= this.lastMessageId) && (!d[3] || !d[3]['offline_messsage'])) {
					console.debug("%o Dropping message older than lastMessageId %d", d, this.lastMessageId);
					return;
				}

				var ins_order = 50;
				if (d[1] == 'agent.ticket-updated') {
					ins_order = 55;
				}

				if (!ordered[ins_order]) {
					ordered[ins_order] = [];
					orders.push(ins_order);
				}

				ordered[ins_order].push(d);

				if (d[0] && d[0] > this.lastMessageId) {
					this.lastMessageId = d[0];
				}
			}
		}

		orders.sort(function(a,b){return a - b});
		for (i = 0; i < orders.length; i++) {
			messages = ordered[orders[i]];
			for (x = 0; x < messages.length; x++) {
				d = messages[x];

				try {
					this.sendMessage(d[1], d[2]);
				} catch (err) {
					DpErrorLog.logError('[AjaxChanneler] ' + err, err.stack || '', err.fileName || '', err.lineNumber || '');
				}
			}
		}

		if (typeof data.last_id != 'undefined' && parseInt(data.last_id) > this.lastMessageId) {
			this.lastMessageId = parseInt(data.last_id);
		}

		this.fireEvent('postMessageSend', [data]);
	},

	getLastMessageId: function() {
		return this.lastMessageId;
	},

	setLastMessageId: function(messageId) {
		this.lastMessageId = messageId;
	},

	//#########################################################################
	//# Handle subcriptions
	//#########################################################################

	subscribeChannel: function(channel, callback, context) {
		this._add_subs.include(channel);

		if (this._add_subs_timeout) {
			window.clearTimeout(this._add_subs_timeout);
		}

		this._add_subs_timeout = this._sendSubscribeChannels.delay(10, this);

		if (callback) {
			this.messageBroker.addMessageListener(channel, callback, context);
		}
	},

	_sendSubscribeChannels: function() {
		var data = [];
		Array.each(this._add_subs, function(v){
			data.push({ name: 'channels[]', value: v });
		});
		this._add_subs = [];

		$.ajax({
			url: this.options.ajaxSubscribeUrl,
			type: 'POST',
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				this._doneSubscribeChannels(data.subscribed_channels);
			}
		});
	},

	unsubscribeChannel: function(channel) {
		this._del_subs.include(channel);

		if (this._del_subs_timeout) {
			window.clearTimeout(this._del_subs_timeout);
		}

		this._del_subs_timeout = this._sendUnsubscribeChannels.delay(10, this);
	},

	_sendUnsubscribeChannels: function() {
		var data = [];
		Array.each(this._del_subs, function(v){
			data.push({ name: 'channels[]', value: v });
		});
		this._del_subs = [];

		$.ajax({
			url: this.options.ajaxUnsubscribeUrl,
			type: 'POST',
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				this._doneUnsubscribeChannels(data.unsubscribed_channels);
			}
		});
	}
});
