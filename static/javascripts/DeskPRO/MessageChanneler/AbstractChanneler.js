Orb.createNamespace('DeskPRO.MessageChanneler');

/**
 * A message channeler handles subscribing to channels, and delivering messages
 * from the server to the message broker, which in turn notifies any listeners.
 */
DeskPRO.MessageChanneler.AbstractChanneler = new Class({

	Implements: [Options],

	messageBroker: null,
	channels: [],

	options: {},

	initialize: function(messageBroker, options) {
		this.messageBroker = messageBroker;
		this._init();
	},

	_init: function() { /* Child class hook method */ },

	subscribeChannel: function(channel) {
		// Override
	},

	_doneSubscribeChannels: function(channels) {
		Array.each(channels, function(c) {
			this.channels.include(c);
		}, this);
	},

	unsubscribeChannel: function(channel) {
		// Override
	},

	_doneSubscribeChannels: function(channels) {
		Array.each(channels, function(c) {
			this.channels.erase(c);
		}, this);
	},

	sendMessage: function(channel, message) {
		this.messageBroker.sendMessage(channel, message);
	}
});