var babelJest = require('babel-jest');
var path = require('path');

module.exports = {
  process: function(src, filename) {
    var result = this.compileDefines(src);
    result = this.fixPaths(result, filename);
    result = babelJest.process(result, filename);

    return result;
  },

  // process macros: "// #define ~Alias Value"
  compileDefines: function(result) {
    const regex = /^\/\/ \#define (\~.+) (.+)$/gm;

    var define;
    do {
      if (define = regex.exec(result)) {
        result = result.replace(new RegExp(define[1], 'g'), define[2]);
      }
    }
    while (define);

    return result;
  },

  // Fix absolute paths to relative for source imports and tests dontMock and require
  fixPaths: function(result, filename) {
    var index = filename.indexOf(path.sep + 'pub' + path.sep + 'src');
    if (index > -1) {
      var relative = filename.substr(index);
      var matches = relative.split(path.sep);
      var depth = matches.length - 4;
      var margin = '../'.repeat(depth);

      // DeskPRO/... paths ---------------------------------------------------------------------------------------------

      // single line imports
      result = result.replace(
        /^import(.*)[\"\']DeskPRO(.*)[\"\'](.*)$/gm,
        "import$1'" + margin + "DeskPRO$2'$3"
      );

      // multi line imports
      result = result.replace(
        /import([^;]+?)from([^;]+?)[\"\']DeskPRO(.*?)[\"\'](.*?);/gm,
        "import$1 from $2'" + margin + "DeskPRO$3'$4"
      );

      result = result.replace(
        /^jest\.dontMock\([\"\']DeskPRO(.*)[\"\']\)(.*)$/gm,
        "jest.dontMock('" + margin + "DeskPRO$1')$2"
      );

      result = result.replace(
        /^jest\.mock\([\"\']DeskPRO(.*)[\"\']\)(.*)$/gm,
        "jest.mock('" + margin + "DeskPRO$1')$2"
      );
      result = result.replace(
        /^(.+)([\s=])require\([\"\']DeskPRO(.*)[\"\']\)(.*)$/gm,
        "$1$2require('" + margin + "DeskPRO$3')$4"
      );

      // Ampliflux/... paths -------------------------------------------------------------------------------------------

      result = result.replace(
        /^import(.*)[\"\']Ampliflux(.*)[\"\'](.*)$/gm,
        "import$1'" + margin + "DeskPRO/Component/Ampliflux$2'$3"
      );

      // Helpers/... paths ----------------------------------------------------------------------------------------

      result = result.replace(
        /^import(.*)[\"\']Helpers(.*)[\"\'](.*)$/gm,
        "import$1'" + margin + "tests/Helpers$2'$3"
      );
      result = result.replace(
        /^(.+)([\s=])require\([\"\']Helpers(.*)[\"\']\)(.*)$/gm,
        "$1$2require('" + margin + "tests/Helpers$3')$4"
      );

      // DemoState/... paths ----------------------------------------------------------------------------------------

      result = result.replace(
        /^import(.*)[\"\']DemoState(.*)[\"\'](.*)$/gm,
        "import$1'" + margin + "tests/DemoState$2'$3"
      );
      result = result.replace(
        /^(.+)([\s=])require\([\"\']DemoState(.*)[\"\']\)(.*)$/gm,
        "$1$2require('" + margin + "tests/DemoState$3')$4"
      );
    }

    return result;
  }
};
