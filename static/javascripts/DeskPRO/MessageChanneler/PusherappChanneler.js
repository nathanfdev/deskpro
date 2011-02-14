Orb.createNamespace('DeskPRO.MessageChanneler');

/**
 * The pusherapp channeler uses websockets and pusherapp.com to delivery messages.
 *
 * Note that pusherapp defines both channels and events, while DeskPRO's defintion of
 * a "channel" implicitly defines an event name as well in its channel name.
 *
 * Because permissions and whatnot need to be enforced, as well as various state
 * (ie., only some users care about some actions), we use a separate pusherapp channel
 * for every client, and then each event within the channel is DeskPRO's concept of a "channel".
 *
 * So we do not use pusherapps routing system which splits up channels and events. We use just
 * one singluar channel, and then the messagebroker takes care of proper routing to listeners based
 * on the event name.
 */
DeskPRO.MessageChanneler.PusherappChanneler = new Class({
	Extends: DeskPRO.MessageChanneler.AbstractChanneler,

	socket: null,
	_init: function() {
		this.socket = new Pusher(this.options.apiKey);

		var channel = this.socket.subscribe('client-' + this.options.privateChannelId);
		channel.bind_all(this.handleMessage.bind(this));
	},

	handleMessage: function(event_name, data) {
		var name = this.channelToPusherapp(event_name);
		this.sendMessage(name, data);
	},

	/**
	 * Names in pusherapp use dashes to separate words, we use dots.
	 *
	 * @param name
	 * @return string
	 */
	channelToPusherapp: function(name) {
		return name.replace('-', '.');
	},


	//#########################################################################
	//# Handle subcriptions
	//#########################################################################

	_add_subs: [],
	_add_subs_timeout: null,
	subscribeChannel: function(channel) {
		this._add_subs.include(channel);

		if (this._add_subs_timeout) {
			window.clearTimout(this._add_subs_timeout);
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
			window.clearTimout(this._del_subs_timeout);
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