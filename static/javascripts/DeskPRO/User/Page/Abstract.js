Orb.createNamespace('DeskPRO.User.Page');

/**
 * Basic page handler
 */
DeskPRO.User.Page.Abstract = new Orb.Class({
	initialize: function(options) {
		this.options = this.getDefaultOptions();
		
		if (options) {
			this.setOptions(options);
		}

		this.init();
	},

	
	/**
	 * Empty hook method for children to implement init code
	 */
	init: function() {

	},


	/**
	 * This method is called ondomready usually, it should init interface elements
	 */
	initPage: function() {
		this.initFeatures();
	},


	/**
	 * Default options values for this window
	 */
	getDefaultOptions: function() {
		return {};
	},



	/**
	 * This initiates common interface elements elements within a container element.
	 * 
	 * @param parent
	 */
	initFeatures: function(parent) {
		if (!parent) parent = document.body;

		$('.timeago', parent).timeago();
	}
});