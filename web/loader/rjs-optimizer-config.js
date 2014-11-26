exports.getConfig = function() {
    return {
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
};