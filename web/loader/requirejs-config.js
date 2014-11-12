(function() {
	var config = {
		"baseUrl": DP_ASSET_URL,
		"waitSeconds": 60,
		"urlArgs": (DP_IS_DEBUG ? "bust=" + (new Date()).getTime() : "v=" + (DP_BUILD_TIME || "0")),
		"paths": !!include('paths.json'),
		"shim": !!include('shims.json'),
		"priority": [
			"jquery",
			"angular"
		]
	};

	if (!DP_IS_DEBUG || DP_USE_RJS_BUILD) {
		config.paths["AdminLoad"]        = "app-build/Admin/AdminLoad.min";
		config.paths["CloudAdminLoad"]   = "app-build/Admin/Cloud/CloudAdminLoad.min";
		config.paths["AdminUpgradeLoad"] = "app-build/AdminUpgrade/AdminUpgradeLoad.min";
		config.paths["AdminStartLoad"]   = "app-build/AdminStart/AdminStartLoad.min";
		config.paths["ReportsLoad"]      = "app-build/Reports/ReportsLoad.min";
	}

	requirejs.config(config);

	if (window.DP_INTERFACE_LOADER) {
		requirejs([window.DP_INTERFACE_LOADER], function(Loader) {
			Loader.start();
		});
	}
})();
