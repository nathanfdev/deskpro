define([
  'angular',
  'Portal/App/App',
  'jquery'
], function(angular) {

  if (!window.console) {
    window.console = {
      log: function(){},
      warn: function(){},
      error: function(){}
    }
  }

  return {
    start: function() {
      var $html = angular.element(document.getElementsByTagName('html')[0]);

      angular.element().ready(function() {
        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          var module = angular.module('Portal_App');
          for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
            module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        angular.bootstrap($html, ['Portal_App']);
      });
    }
  }
});