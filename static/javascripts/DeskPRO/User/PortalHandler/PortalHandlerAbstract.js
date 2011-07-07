Orb.createNamespace('DeskPRO.User.PortalHandler');

DeskPRO.User.PortalHandler.PortalHandlerAbstract = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {};
		if (options) {
			this.setOptions(options);
		}

		this.el = null;
		if (this.options.el) {
			this.el = $(this.options.el);
		}

		this.init();
	},


	/**
	 * Empty hook method for children to implement init code
	 */
	init: function() {

	}
});