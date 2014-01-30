define(['javascripts/DeskPRO/App/Controller/BaseController'], function(BaseController) {

	//##################################################################################################################
	//# Helpers
	//##################################################################################################################

	function classExtend(childObj, parentObj)
	{
		var tmpObj = function () {}
		tmpObj.prototype = parentObj.prototype;
		childObj.prototype = new tmpObj();
		childObj.prototype.constructor = childObj;
		return childObj;
	}

	/**
	 * Gets the prototype for a package
	 * @param {String} packageName
	 * @returns {Function}
	 */
	function getControllerClass(packageClass)
	{
		var proto, k;
		if (typeof packageClass == 'function') {
			proto = classExtend(packageClass, BaseController)
		} else {
			proto = function() {}

			for (k in packageClass) {
				if (packageClass.hasOwnProperty(k)) {
					proto.prototype[k] = packageClass[k];
				}
			}

			proto = classExtend(proto, BaseController)
		}

		return proto;
	};

	//##################################################################################################################
	//# BaseAppPackage
	//##################################################################################################################

	function BaseAppPackage() {}

	BaseAppPackage.prototype._initContext = function(context) {
		this._id          = context.id;
		this._settings    = context.settings;
		this._packageName = context.packageName;
		this._controllers = [];
	};

	BaseAppPackage.prototype.init = function() { };


	/**
	 * Registers a new controller
	 *
	 * @param {Object} controllerClass
	 * @param {Object} controllerInstanceInfo
	 */
	BaseAppPackage.prototype.registerController = function(controllerClass, controllerInstanceInfo) {
		var ctrlInstance;

		controllerInstanceInfo.appInstance = this;
		controllerClass = getControllerClass(controllerClass);
		ctrlInstance = new controllerClass();
		ctrlInstance._initContext(appInstanceInfo);
		ctrlInstance.init();

		this._controllers.push(ctrlInstance);
		return ctrlInstance;
	};


	/**
	 * Get the app package name
	 * @returns {String}
	 */
	BaseAppPackage.prototype.getPackageName = function() {
		return this._packageName;
	};


	/**
	 * Get the AppInstance ID
	 * @returns {Integer}
	 */
	BaseAppPackage.prototype.getId = function() {
		return this._id;
	};


	/**
	 * Get all settings
	 * @returns {Object}
	 */
	BaseAppPackage.prototype.getAllSettings = function() {
		return this._settings;
	};


	/**
	 * Get a single setting
	 * @param {String} k  The setting name
	 * @param {Mixed}  def_val  The default value to return if k doesnt exist
	 * @returns {Mixed}
	 */
	BaseAppPackage.prototype.getSetting = function(k, def_val) {
		if (typeof this._settings[k] == 'undefined') {
			return def_val;
		}

		return this._settings[k];
	};

	return BaseAppPackage;
});