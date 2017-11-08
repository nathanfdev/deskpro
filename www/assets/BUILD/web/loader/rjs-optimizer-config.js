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

  return config;
};