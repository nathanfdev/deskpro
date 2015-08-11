var fs   = require("fs");
var path = require("path");
var glob = require("glob");

// Map of path => timestamp
var updatedPaths = {};

function writeAutoIndex(bundlePath) {
  var importLines    = [];
  var packNames      = [];
  var bundleName     = bundlePath.replace(/.*?\/Bundle\/(.*?)Bundle\/?$/, '$1');
  var outputFile     = bundlePath + "/" + bundleName + "App_Reducers.js";

  var files = glob.sync("**/Reducers/index.js", { cwd: bundlePath, root: bundlePath });
  files.forEach(function (f) {
    var name = f.replace(/^.*?Modules\/(\w+)\/.*?$/, '$1') + 'Stores';
    importLines.push("import * as " + name + " from './" + f + "';");
    packNames.push(name);
  });

  var header = "// This file is auto-generated based on the\n// files that are present in this directory.\n\n// Do NOT manually edit this file. Your changes will be overwritten."

  var index = header + "\n\n" + importLines.join("\n") +
    "\n\n" +
    "export default Object.assign({}, \n" +
    "  " + packNames.join(",\n  ") +
    "\n);" +
    "\n"
  ;
  fs.writeFileSync(outputFile, index);

  console.log("Wrote: " + outputFile);
  console.log("\t-> " + packNames.join(', '));

  return;
}

function loader(code) {
  this.cacheable && this.cacheable()

  // SomeApp.js
  var bundlePath = this.resourcePath.replace(/^(.*?\/Bundle\/.+?Bundle)\/\w+App.js$/, '$1');

  var ts = (new Date()).getTime();
  if (!updatedPaths[bundlePath]) {
    updatedPaths[bundlePath] = 0;
  }

  // Only update if its been changed in the last 1s
  // This is to prevent the same file from being written
  // many times on the initial load
  if (updatedPaths[bundlePath] < (ts-1000)) {
    updatedPaths[bundlePath] = ts;
    writeAutoIndex(bundlePath);
  }

  return code;
}

module.exports = loader;
