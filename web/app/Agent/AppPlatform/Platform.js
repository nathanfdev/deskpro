define([
	'Agent/AppPlatform/Context/AppContext',
	'Agent/AppPlatform/Context/TabContext/TabContext',
	'Agent/AppPlatform/Context/TabContext/TicketTabContext',
], function(AppContext) {
	return new Orb.Class({
		initialize: function(ngModule, appsConfig) {
			this.ngModule   = ngModule;
			this.apps       = [];
			this.assetPaths = {};
			this.isStarted  = false;

			if (appsConfig) {
				for (var i = 0; i < appsConfig.length; i++) {
					this.registerApp(appsConfig[i].contextClass, appsConfig[i]);
				}
			}
		},

		start: function() {
			var i;

			if (this.isStarted) return;
			this.isStarted = true;

			for (i = 0; i < this.apps.length; i++) {
				this.apps[i]._dp_init();
			}
		},


		/**
		 * Register a new app
		 *
		 * @param {Object} contextClass
		 * @param {Object} appInstanceInfo
		 * @return {AppContext}
		 */
		registerApp: function(contextClass, appInstanceInfo) {
			var appContext, i;

			if (appInstanceInfo.contextClass) {
				delete appInstanceInfo.contextClass;
			}

			appInstanceInfo.platform = this;

			if (typeof contextClass == 'function') {
				appContext = new contextClass(appInstanceInfo);
			} else {
				if (typeof contextClass.Extends == 'undefined') {
					contextClass.Extends = AppContext
				}
				contextClass = new Orb.Class(contextClass)
				appContext = new contextClass(appInstanceInfo);
			}

			this.apps.push(appContext);

			// Copy assets to our local assetsPath
			for (i in appInstanceInfo.assets) {
				if (appInstanceInfo.assets.hasOwnProperty(i)) {
					this.assetPaths[i] = appInstanceInfo.assets[i];
				}
			}

			if (this.isStarted) {
				appContext._dp_init();
			}

			return appContext;
		},


		/**
		 * Called when a new fragment is created
		 * @param {Object} fragment
		 */
		onFragmentStarted: function(fragment) {
			var i;
			for (i = 0; i < this.apps.length; i++) {
				this.apps[i].startFragmentContexts(fragment);
			}
		},


		/**
		 * Called when a fragment is closed.
		 * @param {Object} fragment
		 */
		onFragmentEnded: function(fragment) {
			var i;
			for (i = 0; i < this.apps.length; i++) {
				this.apps[i].cleanupFragmentContexts(fragment);
			}
		},


		/**
		 * Get the real file path for a certain asset
		 * @param {String} aliasPath
		 * @returns {String}
		 */
		getAssetPath: function(aliasPath) {
			if (!this.assetPaths[aliasPath]) {
				return null;
			}

			return this.assetPaths[aliasPath];
		},


		/**
		 * Get the angular module
		 * @returns {Object}
		 */
		getNgModule: function() {
			return this.ngModule;
		},


		/**
		 * Get the angular injector
		 * @returns {injector}
		 */
		getNgInjector: function() {
			return this.ngModule.dpInjector;
		}
	});
});