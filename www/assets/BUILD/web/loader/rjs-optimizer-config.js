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
  };

  // Skip optimization of the following
  config.paths.spectrum = 'empty:';
  config.paths.angularSpectrumColorpicker = 'empty:';

  var amchartsConfig = {
    "amcharts.pie": {"exports": "AmCharts", "deps": ["amcharts"], "init": function(){AmCharts.isReady = true;}},
    "amcharts.serial": {"exports": "AmCharts", "deps": ["amcharts"], "init": function(){AmCharts.isReady = true;}}
  };
  for (var key in amchartsConfig) {
    if (amchartsConfig.hasOwnProperty(key)) {
      config.shim[key] = amchartsConfig[key];
    }
  }
  
  return config;
};