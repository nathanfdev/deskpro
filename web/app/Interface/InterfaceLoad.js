define([
  'angular',
  'Interface/App/InterfaceApp'
], function(angular, InterfaceApp) {

  if (!window.console) {
    window.console = {
      log: function(){},
      warn: function(){},
      error: function(){}
    }
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
      }
    },

    boot: function() {
      this.isDoneBoot = true;
      angular.bootstrap(document.getElementById('app_win'), ['DeskPRO.InterfaceApp']);
    }
  }
});