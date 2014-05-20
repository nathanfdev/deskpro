define([
	'DeskPRO/App/Context/TabContext/TicketTabContext',
	'DeskPRO/App/Context/TabContext/UserTabContext',
	'DeskPRO/App/Context/TabContext/OrgTabContext',
], function(
	TicketTabContext,
	UserTabContext,
	OrgTabContext
) {
	return new Orb.Class({
		initialize: function(contextParams) {
			this._appId          = contextParams.id;
			this._platform       = contextParams.platform;
			this._packageName    = contextParams.packageName;
			this._scopeName      = contextParams.scope;
			this._settings       = contextParams.settings;
			this._regControllers = {};
			this._createdControllers = {};
		},


		/**
		 * Called automatically when the context is created
		 */
		init: function() {

		},

		_dp_init: function() {
			this.init();
		},


		/**
		 * Gets the app ID
		 *
		 * @return {Integer}
		 */
		getAppId: function() {
			return this._appId;
		},


		/**
		 * Get the app package name
		 *
		 * @returns {String}
		 */
		getPackageName: function() {
			return this._packageName;
		},


		/**
		 * Get the platform
		 * @returns {Platform}
		 */
		getPlatform: function() {
			return this._platform;
		},


		/**
		 * Gets the app scope
		 *
		 * @return {String}
		 */
		getScopeName: function() {
			return this._scopeName;
		},


		/**
		 * Gets a setting
		 * @param {String} name
		 * @param {mixed} defaultValue
		 * @return {mixed}
		 */
		getSetting: function(name, defaultValue) {
			if (typeof this._settings[name] == 'undefined') {
				return defaultValue;
			}
			return this._settings[name];
		},


		/**
		 * Return the URL to request handler (for native apps with request handlers)
		 *
		 * @param {String}    type   The type: just "agent" at the moment
		 * @param {String}    action Optional sub-action to pass to the handler in the context
		 * @return {String}
		 */
		getRequestHandlerUrl: function(type, action) {
			var url;
			if (type != 'agent') {
				throw "Invalid `type` (must be 'agent')";
			}

			url = window.BASE_URL + type + "/apps/" + this.getAppId();
			if (action) {
				action = action.replace(/^\/+/, '');
				url += "/" + action;
			}

			return url;
		},


		/**
		 * Regster a context for a type of tab
		 *
		 * @param {String} type
		 * @param {Object} context
		 * @param {Object} params
		 * @return {TabContext}
		 */
		register: function(type, context, params) {
			var baseClass;

			if (typeof context != 'function') {
				switch (type) {
					case 'ticket':
						baseClass = TicketTabContext;
						break;
					case 'user':
					case 'person':
						type = 'person';
						baseClass = UserTabContext;
						break;
					case 'organization':
					case 'org':
						type = 'organization';
						baseClass = OrgTabContext;
						break;
				}

				if (baseClass) {
					context.Extends = baseClass;
					context = Orb.Class(context)
				}
			}

			if (!this._regControllers[type]) {
				this._regControllers[type] = [];
			}

			this._regControllers[type].push([context, params || null]);

			return context;
		},


		/**
		 * Shortcut for registering an empty context that just renders a template to a tab location.
		 *
		 * @param {String} type
		 * @param {String/Array} location
		 * @param {Function} controller
		 * @param {Object} params
		 * @return {TabContext}
		 */
		registerWidget: function(type, location, templateName, controller, params) {
			return this.register(type, {
				init: function() {
					this.renderTemplate(location, templateName, controller, params);
				}
			}, params);
		},


		/**
		 * Shortcut for registering an empty context that just renders a template to a tab location.
		 *
		 * @param {String} type
		 * @param {String/Array} location
		 * @param {Function} controller
		 * @param {Object} params
		 * @return {TabContext}
		 */
		registerWidgetTab: function(type, location, tabTitle, templateName, controller, params) {
			return this.register(type, {
				init: function() {
					this.renderTemplateTab(location, tabTitle, templateName, controller, params);
				}
			}, params);
		},


		/**
		 * Called by the paltform when a new tab is opened. This is where app controllers are created.
		 * By default, this just runs through the controllers registered with register().
		 *
		 * @param {Object} frag The fragment that was opened
		 * @returns {Array} Array of contorllers that were created
		 */
		startFragmentContexts: function(frag) {
			var i, contextParams, ctrl, created = [], type = frag.TYPENAME;

			if (!this._regControllers[type]) {
				return created;
			}

			this._createdControllers[frag.OBJ_ID] = [];

			contextParams = {
				appContext: this,
				fragment: frag
			};

			for (i = 0; i < this._regControllers[type].length; i++) {
				// 0 = class, 1 = params
				ctrl = new this._regControllers[type][i][0](
					contextParams,
					this._regControllers[type][i][1]
				);

				created.push(ctrl);
				this._createdControllers[frag.OBJ_ID].push(ctrl);
			}

			return created;
		},


		/**
		 * Called by the platform when a tab is closed. This is where controllers are
		 * destroyed. By default, this runs through the controllers that were created in startFragmentContexts.
		 *
		 * @param {Object} frag The fragment that was closed
		 * @returns {Array} Array of controllers that were cleaned up
		 */
		cleanupFragmentContexts: function(frag) {
			var i, controllers;
			if (!this._createdControllers[frag.OBJ_ID]) {
				return [];
			}

			controllers = this._createdControllers[frag.OBJ_ID];
			delete this._createdControllers[frag.OBJ_ID];

			for (i = 0; i < controllers.length; i++) {
				controllers[i].destroy();
			}

			return controllers;
		},


		/**
		 * @return $http
		 */
		getHttp: function() {
			return this.getPlatform().getNgInjector().get('$http');
		},

		/**
		 * @return $q
		 */
		getQ: function() {
			return this.getPlatform().getNgInjector().get('$q');
		},


		/**
		 * @return {deferred}
		 */
		createDeferred: function() {
			return this.getQ().defer();
		}
	});
});