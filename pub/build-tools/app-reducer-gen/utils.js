var fs      = require("fs");
var path    = require("path");
var glob    = require("glob");
var _       = require("lodash");
var sprintf = require("sprintf-js").sprintf;

/**
 * Read all reducers in XyzBundle app dir
 *
 * @param {String} appName       The app name ('Xyz' in 'XyzBundle')
 * @param {String} bundlePath    The full path to the bundle
 * @param {Array}  readReducers  An array that will contain the files read
 */
function handleBundle(appName, bundlePath, readReducers) {
  var iter = function(moduleName, modulePath, pathParts) {
    var files = glob.sync('');
  };

  /*
    hierarchy is:
    {
      Something: {
        sub: {
          sub
        }
      }
    }
  */
  var hierarchy  = {};
  var importLines = [];
  var exportLines = [];
  var legacyModules = {};

  if (!readReducers) {
    readReducers = [];
  }

  glob.sync('{**/Modules/*/,**/Modules/*/RecordStores/}', { cwd: bundlePath, root: bundlePath }).forEach(function(d) {
    var modulePath = bundlePath + '/' + d;
    var moduleName = path.basename(modulePath);

    if (fs.existsSync(modulePath + 'Reducers/legacy_reducers.txt')) {
      legacyModules[moduleName] = true;
    }

    glob.sync("Reducers/**/*.js", { cwd: modulePath, root: modulePath }).forEach(function(f) {
      var filePath    = modulePath + f;
      var relPath     = filePath.replace(bundlePath, '.');
      var fileName    = path.basename(filePath);
      var reducerName = path.basename(filePath, '.js');
      var rPath       = path.dirname(filePath).replace(modulePath+'Reducers', '').replace(/^\//, '').replace(/\/$/, '').split("/").filter(function(v) { return v !== ''; });

      readReducers.push(filePath);

      if (fileName.indexOf('.spec.') !== -1 || fileName === 'index.js') {
        return false;
      }

      var names = [moduleName];

      // Create additional hierarchy level equal to enclosing directory name within RecordStores
      if (moduleName === 'RecordStores') {
        var dirs = modulePath.split(path.sep);
        names.push(dirs[dirs.length - 3]);
      }

      // Legacy modules have all their reducers in root level
      if (legacyModules[moduleName]) {
        names.pop();
        names.push("ROOT");
      }

      rPath.forEach(function(p) {
        names.push(p);
      });

      var parentPath = names.slice(0).join('.');
      names.push(reducerName);
      var fullName = names.join('_');

      if (!_.has(hierarchy, parentPath)) {
        hierarchy = _.set(hierarchy, parentPath, {});
      }

      _.get(hierarchy, parentPath)[reducerName] = fullName;

      importLines.push(sprintf("import %-50s from \"%s\";", fullName, relPath));
    });
  });

  var iter = function(o, depth) {
    var indent = _.repeat('  ', depth);

    _.forOwn(o, function(v, k) {
      if (_.isPlainObject(v)) {
        if (k === "ROOT") {
          iter(v, depth);
        } else {
          exportLines.push(indent + "\"" + k + "\": {\n");
          iter(v, depth + 1);
          exportLines.push(indent + "},\n");
        }
      } else {
        var keyPart = indent + "\"" + k + "\":";
        exportLines.push(sprintf("%-57s %s,\n", keyPart, v));
      }
    });
  }

  exportLines.push("export default {\n");
  iter(hierarchy, 1);
  exportLines.push("};");

  var res = importLines.join("\n") + "\n\n" + exportLines.join("") + "\n";

  var reducerFile = bundlePath + '/' + appName + 'App_Reducers.js';
  fs.writeFileSync(reducerFile, res);
}

module.exports = {
  handleBundle:  handleBundle
};
