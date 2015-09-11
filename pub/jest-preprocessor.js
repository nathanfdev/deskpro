var babelJest = require('babel-jest');
var path = require('path');

module.exports = {
  process: function(src, filename) {
    var result = this.fixPaths(src, filename);
    result = babelJest.process(result, filename);

    return result;
  },

  fixPaths: function(result, filename) {

    // Fix absolute paths to relative for source imports and tests dontMock and require

    var index = filename.indexOf(path.sep + 'pub' + path.sep + 'src');
    if (index > -1) {
      var relative = filename.substr(index);
      var matches = relative.split(path.sep);
      var depth = matches.length - 4;
      var margin = '../'.repeat(depth);

      // DeskPRO/... paths ---------------------------------------------------------------------------------------------

      result = result.replace(
        /^import(.*)[\"\']DeskPRO(.*)[\"\'](.*)$/gm,
        "import$1'" + margin + "DeskPRO$2'$3"
      );
      result = result.replace(
        /^jest\.dontMock\([\"\']DeskPRO(.*)[\"\']\)(.*)$/gm,
        "jest.dontMock('" + margin + "DeskPRO$1')$2"
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
        /^(.+)([\s=])require\([\"\']Helpers(.*)[\"\']\)(.*)$/gm,
        "$1$2require('" + margin + "tests/Helpers$3')$4"
      );
    }

    return result;
  }
};
