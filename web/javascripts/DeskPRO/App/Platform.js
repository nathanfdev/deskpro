define([
	'DeskPRO/App/Context/AppContext',
	'DeskPRO/App/Context/TabContext/TabContext',
	'DeskPRO/App/Context/TabContext/TicketTabContext',
], function(AppContext) {

	var apps = [], AppPlatform, isStarted = false;

	/**
	 * Register a new app
	 *
	 * @param {Object}  packageClass The package class
	 * @param {Integer} id          The app ID
	 * @param {Object}  settings    Settings for the app
	 */
	function registerApp(packageClass, appInstanceInfo)
	{
		var appContext;
		if (typeof packageClass == 'function') {
			appContext = new packageClass(appInstanceInfo);
		} else {
			if (typeof packageClass.Extends == 'undefined') {
				packageClass.Extends = AppContext
			}
			packageClass = new Orb.Class(packageClass)
			appContext = new packageClass(appInstanceInfo);
		}

		apps.push(appContext);

		if (isStarted) {
			appContext._dp_init();
		}

		return appContext;
	}

	function start()
	{
		var i;

		if (isStarted) return;
		isStarted = true;

		for (i = 0; i < apps.length; i++) {
			apps[i]._dp_init();
		}
	}

	AppPlatform = {
		registerApp: registerApp,
		start:       start
	};

	window.AppPlatform = AppPlatform;

	if (window.DeskPRO_Window) {
		window.DeskPRO_Window.initAppPlatform(AppPlatform);
	}

	return AppPlatform;
});