define([
  'angular',
  'AgentApp',
  'AppPlatform',
  'AppPlatformConfig'
], function(angular, AgentApp, AppPlatform, AppPlatformConfig) {

  if (!window.console) {
    window.console = {
      log: function(){},
      warn: function(){},
      error: function(){}
    }
  }

  return {
    start: function() {
      window.DP_UID_COUNTER = 0;
      window.dp_get_uid = function() {
        return window.DP_UID_COUNTER++;
      };
      var $html = angular.element(document.getElementsByTagName('html')[0]);

      angular.element().ready(function() {
        var module = angular.module('AgentApp');
        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
            module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        module.run(['$timeout', '$injector', function($timeout, $injector) {

          // Legacy vars
          AgentApp.dpInjector = $injector;
          window.AppPlatform = new AppPlatform(AgentApp, AppPlatformConfig);

          $timeout(function() {
            window.DP_ONLOAD();

            if (window.DeskPRO_Window) {
              window.DeskPRO_Window.initAppPlatform(window.AppPlatform);
            }
          })
        }]);

        angular.bootstrap($html, ['AgentApp']);
        angular.resumeBootstrap();
      });
    }
  }
});