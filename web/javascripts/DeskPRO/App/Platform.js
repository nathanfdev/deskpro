define([
	'javascripts/DeskPRO/App/BaseAppPackage',
	'javascripts/DeskPRO/App/Controller/BaseController',
], function(BaseAppPackage, BaseController) {

	var apps = [], AppPlatform;

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
	function getPackageClass(packageClass)
	{
		var proto, k;
		if (typeof packageClass == 'function') {
			proto = classExtend(packageClass, BaseAppPackage)
		} else {
			proto = function() {}

			for (k in packageClass) {
				if (packageClass.hasOwnProperty(k)) {
					proto.prototype[k] = packageClass[k];
				}
			}

			proto = classExtend(proto, BaseAppPackage)
		}

		return proto;
	};


	/**
	 * Register a new app
	 *
	 * @param {Object}  packageClass The package class
	 * @param {Integer} id          The app ID
	 * @param {Object}  settings    Settings for the app
	 */
	function registerApp(packageClass, appInstanceInfo)
	{
		var appInstance;

		packageClass = getPackageClass(packageClass);
		appInstance = new packageClass();
		appInstance._initContext(appInstanceInfo);
		appInstance.init();

		apps.push(appInstance);
		return appInstance;
	}


	AppPlatform = {
		registerApp:     registerApp,
		getApps:         function() { return apps; },
		start:           function() { }
	};

	window.AppPlatform = AppPlatform;

	if (window.DeskPRO_Window) {
		window.DeskPRO_Window.initAppPlatform(AppPlatform);
	}

	return AppPlatform;
});