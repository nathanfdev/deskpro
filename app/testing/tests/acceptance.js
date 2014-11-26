exports.config = {
  suites: {
    deskpro: "acceptance/DeskPRO/**/*Spec.js",
    deskpro_admin: "acceptance/DeskPRO/Admin/*Spec.js",
    full: "acceptance/**/*Spec.js"
  },
  baseUrl: 'http://localhost:8888',
  maxSessions: 1,
  rootElement: 'html',
  jasmineNodeOpts: {
    isVerbose: true,
    showColors: true,
    includeStackTrace: true
  },
  allScriptsTimeout: 10000,
  seleniumAddress: 'http://127.0.0.1:4444/wd/hub',
  capabilities: {
    browserName: 'chrome'
  },
  onPrepare: function() {
    global.dp          = require('./acceptance/DeskPRO/Common/Util.js');
    global.driver      = browser.driver;
  }
};