// Karma configuration
// Generated on Fri Nov 07 2014 12:24:26 GMT+0000 (GMT)

module.exports = function(config) {
  config.set({
    basePath: '../../',
    frameworks: ['jasmine', 'requirejs'],

    // list of files / patterns to load in the browser
    files: [
      'testing/unit/admin/setup.js',
      {pattern: 'testing/unit/admin/*.js', included: false},
      {pattern: 'testing/unit/admin/**/*.js', included: false}
    ],

    exclude: [],
    preprocessors: {},
    reporters: ['progress'],
    port: 9876,
    colors: true,
    logLevel: config.LOG_INFO,
    autoWatch: false,
    browsers: [],
    singleRun: true
  });
};
