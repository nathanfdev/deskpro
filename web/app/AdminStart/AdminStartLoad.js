define([
  'angular',
  'jstz',

  'AdminStart/App/App',
  'AdminStart/Ctrl/StartBase',
  'AdminStart/Ctrl/Home',
  'AdminStart/Ctrl/Cron',
  'AdminStart/Ctrl/Email',
  'AdminStart/Ctrl/Finish'
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
      window.DP_UID_COUNTER = 0;
      window.dp_get_uid = function() {
        return window.DP_UID_COUNTER++;
      };
      var $html = angular.element(document.getElementsByTagName('html')[0]);

      angular.element().ready(function() {
        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          var module = angular.module('AdminStart_App');
          for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
            module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        angular.bootstrap($html, ['AdminStart_App']);
      });
    }
  }
});