(function () {
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

  if (!window.DP_IS_DEBUG || window.DP_USE_RJS_BUILD) {
    config.paths["AgentLoad"]        = "app-build/Agent/AgentLoad.min";
    config.paths["AdminLoad"]        = "app-build/Admin/AdminLoad.min";
    config.paths["CloudAdminLoad"]   = "app-build/Admin/Cloud/CloudAdminLoad.min";
    config.paths["AdminUpgradeLoad"] = "app-build/AdminUpgrade/AdminUpgradeLoad.min";
    config.paths["AdminStartLoad"]   = "app-build/AdminStart/AdminStartLoad.min";
    config.paths["ReportsLoad"]      = "app-build/Reports/ReportsLoad.min";
  }

  if (window.DP_RJS_PATHS) {
    for (var i = 0; i < DP_RJS_PATHS.length; i++) {
      for (var k in DP_RJS_PATHS[i]) {
        if (DP_RJS_PATHS[i].hasOwnProperty(k)) {
          config.paths[k] = DP_RJS_PATHS[i][k];
        }
      }
    }
  }

  if (window.DP_RJS_PATHS_IGNORE) {
    for (var i = 0; i < DP_RJS_PATHS_IGNORE.length; i++) {
      if (config.paths[DP_RJS_PATHS_IGNORE[i]]) {
        delete config.paths[DP_RJS_PATHS_IGNORE[i]];
      }
    }
  }

  for (var i in config.paths) {
    if (config.paths[i] == 'empty:') {
      delete config.paths[i];
    }
  }

  requirejs.config(config);

  if (window.DP_INTERFACE_LOADER) {
    requirejs([window.DP_INTERFACE_LOADER], function (Loader) {
      Loader.start();
      window.DP_DONE_LOAD = true;
    });
  }
})();
