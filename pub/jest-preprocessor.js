var babelJest = require('babel-jest');

module.exports = {
  process: function(src, filename) {
    var result = babelJest.process(src, filename);

    // TODO
    // fix relative imports such as:
    // import DpApi from 'DeskPRO/Component/Http/DpApi';
    // somehow like this:
    // result.replace(/^require.*\"DeskPRO.*;$/gm, 'full path');

    return result;
  }
};