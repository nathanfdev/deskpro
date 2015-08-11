var fs = require("fs");
var path = require("path");

// Map of path => timestamp
var updatedPaths = {};

function writeAutoIndex(reducerPath) {
  var files          = fs.readdirSync(reducerPath);
  var importLines    = [];
  var exportNames    = [];
  var indexFile      = path.join(reducerPath, "index.js");

  for(var k in files) {
    var file = files[k];
    if(file == 'index.js' || !file.match(/\.js$/)) {
      continue;
    }

    var name = file.substr(0, file.length - 3);

    importLines.push("import " + name + " from './" + name + "';");
    exportNames.push(name);
  }

  var header = "// This file is auto-generated based on the\n// files that are present in this directory.\n\n// Do NOT manually edit this file. Your changes will be overwritten."

  var index = header + "\n\n" + importLines.join("\n") +
    "\n\n" +
    "export default {\n" +
    "  " + exportNames.join(",\n  ") +
    "\n};" +
    "\n"
  ;
  fs.writeFileSync(indexFile, index);

  console.log("Wrote: " + indexFile);
  console.log("\t-> " + exportNames.join(', '));
}

function loader(code) {
  this.cacheable && this.cacheable()

  if (this.resourcePath.indexOf('index.js') !== -1) {
    return code;
  }

  var reducerPath = this.resourcePath.replace(/^(.*?\/Reducers)\/.*?\.js$/, '$1');

  var ts = (new Date()).getTime();
  if (!updatedPaths[reducerPath]) {
    updatedPaths[reducerPath] = 0;
  }

  // Only update if its been changed in the last 1s
  // This is to prevent the same file from being written
  // many times on the initial load
  if (updatedPaths[reducerPath] < (ts-1000)) {
    updatedPaths[reducerPath] = ts;
    writeAutoIndex(reducerPath);
  }

  return code;
}

module.exports = loader;
