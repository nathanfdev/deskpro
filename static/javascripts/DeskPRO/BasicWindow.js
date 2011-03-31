Orb.createNamespace('DeskPRO');

/**
 * Global window object controls some central things
 */
DeskPRO.BasicWindow = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {

		this.DBEUG = {};
		this.options = this.getDefaultOptions();
		this.registry = {};

		this.messageBroker = this.messageBroker = new DeskPRO.MessageBroker();

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

	},

	/**
	 * Default options values for this window
	 */
	getDefaultOptions: function() {
		return {};
	},

	//#################################################################
	//# Global registry, getters
	//#################################################################

	/**
	 * Get a value from the registry.
	 *
	 * @param {String} id The ID of the item
	 * @return mixed
	 */
	get: function(id, def) {
		if (this.registry[id] === undefined) {
			return def;
		}

		return this.registry[id];
	},


	/**
	 * Add or reset a value in the registry.
	 *
	 * @param {String} id The ID of the item
	 * @param mixed value The value of the item
	 */
	set: function(id, value) {
		this.registry[id] = value;
	},


	/**
	 * Get the message broker
	 */
	getMessageBroker: function() {
		return this.messageBroker;
	},


	/**
	 * Get a debug option
	 * 
	 * @param name
	 * @return mixed
	 */
	getDebug: function(name) {
		if (this.DEBUG[name] === undefined) {
			return false;
		}

		return this.DEBUG[name];
	}
});