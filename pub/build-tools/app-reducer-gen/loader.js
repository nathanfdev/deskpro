var handleBundle  = require("./utils").handleBundle;
var sprintf       = require("sprintf-js").sprintf;

// Map of path => timestamp
var updatedPaths = {};

/**
 * Like handleBundle, except will output status info and update updatedPaths
 * map with timestamp info (used from the laoder).
 *
 * @param {String} appName       The app name ('Xyz' in 'XyzBundle')
 * @param {String} bundlepath    The full path to the bundle
 */
function refreshBundle(appName, bundlePath) {

  console.log(sprintf("Writing reducers for %s", appName));

  var readReducers = [];
  handleBundle(appName, bundlePath, readReducers);

  var ts = (new Date()).getTime();
  readReducers.forEach(function(r) {
    updatedPaths[r] = ts;
  });

  console.log(sprintf("  -> %d reducer files", readReducers.length));
}

function loader(code) {
  this.cacheable && this.cacheable();

  // We only need to update if the file is new
  if (!updatedPaths[this.resourcePath]) {
    var bundlePath = this.resourcePath.replace(/^(.*?\/\w+Bundle)\/.*?$/, '$1');
    var appName = bundlePath.replace(/^.*?\/(\w+)Bundle$/, '$1');
    refreshBundle(appName, bundlePath);
  }

  return code;
}

module.exports = {
  loader: loader,
  refreshBundle: refreshBundle
};
