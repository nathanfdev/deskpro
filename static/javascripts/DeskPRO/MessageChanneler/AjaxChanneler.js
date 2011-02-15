Orb.createNamespace('DeskPRO.MessageChanneler');

/**
 * The AJAX channeler uses AJAX polling to fetch new messages from the server.
 */
DeskPRO.MessageChanneler.AjaxChanneler = new Class({
	Extends: DeskPRO.MessageChanneler.AbstractChanneler,

	poller: null,
	lastMessageId: null,

	_init: function() {
		this.poller = new DeskPRO.AjaxPoller.Poller({
			ajaxUrl: this.options.ajaxMessagesUrl,
			interval: 5000,
			ajaxType: 'GET'
		});

		this.poller.addData((function () {
			if (!this.lastMessageId) return null;
			return { 'since': this.lastMessageId };
		}).bind(this), 'since', { recurring: true });

		this.poller.addEvent('ajaxSuccess', this.handleMessageAjax.bind(this));

		if (this.options.lastMessageId) {
			this.lastMessageId = this.options.lastMessageId;
		}
	},

	handleMessageAjax: function(data) {
		if (data.last_id) {
			this.lastMessageId = data.last_id;
		}

		if (data.messages) {
			Array.each(data.messages, function(d) {
				this.sendMessage(d[0], d[1]);
			}, this);
		}
	},


	//#########################################################################
	//# Handle subcriptions
	//#########################################################################

	_add_subs: [],
	_add_subs_timeout: null,
	subscribeChannel: function(channel) {
		this._add_subs.include(channel);

		if (this._add_subs_timeout) {
			window.clearTimeout(this._add_subs_timeout);
		}

		this._add_subs_timeout = this._sendSubscribeChannels.delay(300, this);
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


	_del_subs: [],
	_del_subs_timeout: null,
	unsubscribeChannel: function(channel) {
		this._del_subs.include(channel);

		if (this._del_subs_timeout) {
			window.clearTimeout(this._del_subs_timeout);
		}

		this._del_subs_timeout = this._sendUnsubscribeChannels.delay(300, this);
	},

	_sendUnsubscribeChannels: function() {
		var data = [];
		Array.each(this._add_subs, function(v){
			data.push({ name: 'channels[]', value: v });
		});
		this._add_subs = [];

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