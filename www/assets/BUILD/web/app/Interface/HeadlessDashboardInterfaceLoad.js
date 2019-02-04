define([
  'angular',
  'Interface/App/HeadlessDashboardInterfaceApp',

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
  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',
  window.DP_REPORT_BUNDLE_PATH

], (angular, HeadlessDashboardInterfaceApp) => {
  if (!window.console) {
    window.console = {
      log() {},
      warn() {},
      error() {}
    };
  }

  return {
    start() {
      const self = this;
      this.isDocReady = false;
      this.isAppReady = false;

      angular.element().ready(() => {
        self.isDocReady = true;
        self.bootReady();
      });

      this.isAppReady = true;
      this.bootReady();
    },

    bootReady() {
      if (this.isDocReady && this.isAppReady && !this.isDoneBoot) {
        this.boot();
        $('#dp_loading').remove();
      }
    },

    boot() {
      this.isDoneBoot = true;
      angular.bootstrap(document.getElementById('dp_win'), ['DeskPRO.HeadlessDashboardInterfaceApp']);
    }
  };
});
