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
			if (!this.lastMessageId) return null;
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
		if (data.last_id && data.last_id > this.lastMessageId) {
			this.lastMessageId = data.last_id;
		}

		if (data.messages && data.messages.length) {
			Array.each(data.messages, function(d) {
				if (d[0] && (d[0] <= this.lastMessageId) && (!d[3] || !d[3]['offline_messsage'])) {
					console.debug("%o Dropping message older than lastMessageId %d", d, this.lastMessageId);
					return;
				}

				if (d[0] && d[0] > this.lastMessageId) {
					this.lastMessageId = d[0];
				}

				try {
					this.sendMessage(d[1], d[2]);
				} catch (err) {
					DpErrorLog.logError(err, '', '', '');
				}
			}, this);
		}
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
