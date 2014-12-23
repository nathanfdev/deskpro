exports.getConfig = function() {
    var config = {
      "baseUrl": ".",
      "paths": !!include('paths.json'),
      "shim": !!include('shims.json'),
      "priority": [
      "jquery",
      "angular"
    ],
      "preserveLicenseComments": false,
      "generateSourceMaps": true,
      "optimize": "uglify2",
      "uglify2": {
      "mangle": false
    }
  }
    var amchartsConfig = {
        "amcharts.pie": {"exports": "AmCharts", "deps": ["amcharts"], "init": function(){AmCharts.isReady = true;}},
        "amcharts.serial": {"exports": "AmCharts", "deps": ["amcharts"], "init": function(){AmCharts.isReady = true;}}
    };
    for (var key in amchartsConfig) {
        config.shim[key] = amchartsConfig[key];
    }
    return config;
};