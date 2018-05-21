define([
  'angular',
  'Interface/App/InterfaceApp',

  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angularUiSortable',
  'angular-moment',
  'angularFileUpload',
  'angularSlider',
  'ngFileUpload',

  'moment',

  'jquery',
  'jqueryUi',
  'jquery.fileupload',
  'underscore',
  'stacktrace',
  'handlebars',
  'customEventPolyfill',
  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',
  window.DP_REPORT_BUNDLE_PATH

], function(angular, InterfaceApp) {

  if (!window.console) {
    window.console = {
      log: function(){},
      warn: function(){},
      error: function(){}
    };
  }

  return {
    start: function() {
      var self = this;
      this.isDocReady = false;
      this.isAppReady = false;

      angular.element().ready(function() {
        self.isDocReady = true;
        self.bootReady();
      });

      this.isAppReady = true;
      this.bootReady();
    },

    bootReady: function() {
      if (this.isDocReady && this.isAppReady && !this.isDoneBoot) {
        this.boot();
        $('#dp_loading').remove();
      }
    },

    boot: function() {
      this.isDoneBoot = true;
      angular.bootstrap(document.getElementById('dp_win'), ['DeskPRO.InterfaceApp']);
    }
  };
});