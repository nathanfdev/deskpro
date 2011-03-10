Orb.createNamespace('DeskPRO.Admin');

/**
 * Similar in responsibility to the Agent.Window (global object/init etc), but completely
 * different.
 *
 * Within the admin interface, this is the gloabl var DeskPRO_Window.
 *
 * If a page defines the special function DeskPRO_Window_Init(), it will be called
 * automatically once the page is ready.
 */
DeskPRO.Admin.Window = new Orb.Class({

	Implements: [Orb.Util.Events],

	initialize: function() {

		this.DEBUG = {};
		this.registry = {};
		this.messageBroker = null;
		this.interfaceEffects = null;

	},

	initPage: function() {
		this.interfaceEffects = new DeskPRO.Agent.InterfaceEffects();
		this.interfaceEffects.initPage();

		this._initBasic();
		this._initWindowInterface();

		if (typeof window.DeskPRO_Window_Init == 'function') {
			window.DeskPRO_Window_Init();
		}
	},

	_initBasic: function() {
		this.messageBroker = new DeskPRO.MessageBroker();
	},

	_initWindowInterface: function() {
		var menuOpener = new DeskPRO.Admin.WindowElement.MainMenuOpener();
	},



	//#################################################################
	//# Getters
	//#################################################################

	getMessageBroker: function() {
		return this.messageBroker;
	},

	//#################################################################
	//# Global registry
	//#################################################################

	/**
	 * Get a value from the registry.
	 *
	 * @param {String} id The ID of the item
	 * @return mixed
	 */
	get: function(id) {
		if (this.registry[id] === undefined) {
			return null;
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
	 * Get a URL pattern
	 */
	getUrl: function(name, vars) {
		if (!window.DESKPRO_URL_REGISTRY[name]) {
			console.warn('Unknown url name %s', name);
			return null;
		}

		var url = window.DESKPRO_URL_REGISTRY[name];
		if (vars) {
			Object.each(vars, function(v,k) {
				url = url.replace('{'+k+'}', v);
			});
		}

		return url;
	}
});