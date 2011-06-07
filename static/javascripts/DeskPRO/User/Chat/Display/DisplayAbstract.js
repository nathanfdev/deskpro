Orb.createNamespace('DeskPRO.User.Chat.Display');

/**
 * User chat handler
 */
DeskPRO.User.Chat = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			chat: null
		};

		this.setOptions(options);

		this.chat = this.options.chat;

		this.init();
	},

	init: function() { }
});