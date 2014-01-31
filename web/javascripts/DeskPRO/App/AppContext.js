define(['javascripts/DeskPRO/App/ControllerContext'], function(ControllerContext) {
	var AppContext = function(instanceInfo, controllerClass) {

		var controller;
		var contexts = [];

		this._dp_init = function() {
			controller = new controllerClass(this);
		};

		/**
		 * Get the App instance ID
		 * @return {Integer}
		 */
		this.getAppId = function() {
			return instanceInfo.id;
		};


		/**
		 * Get the package name of the app
		 * @return {String}
		 */
		this.getPackageName = function() {
			return instanceInfo.packageName;
		};


		/**
		 * Get the current scope
		 * @returns {String}
		 */
		this.getScope = function() {
			return instanceInfo.scope;
		};


		/**
		 * Gets the app controller
		 * @returns {controllerClass}
		 */
		this.getController = function() {
			return controller;
		}


		/**
		 * Get a setting
		 * @param {String} name
		 * @param {mixed} defaultValue
		 * @returns {mixed}
		 */
		this.getSetting = function(name, defaultValue) {
			if (typeof instanceInfo.packageName[name] == 'undefined') {
				return defaultValue;
			}
			return instanceInfo.packageName[name];
		};


		/**
		 * Register a controller with the app
		 *
		 * @param ctrlInstanceInfo
		 * @param ctrlClass
		 * @returns {ControllerContext}
		 */
		this.registerController = function(ctrlInstanceInfo, ctrlClass) {
			var controllerContext;

			controllerContext = new ControllerContext(this, ctrlInstanceInfo, ctrlClass);
			this.contexts.push(controllerContext);
			return controllerContext;
		};
	};

	return AppContext;
});