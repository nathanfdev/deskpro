define([
  'angular',

  'AdminUpdateWatcher/App/App',
  'AdminUpdateWatcher/Ctrl/Main'
], (angular) => {
  if (!window.console) {
    window.console = {
      log() {},
      warn() {},
      error() {}
    };
  }

  return {
    start() {
      window.DP_UID_COUNTER = 0;
      window.dp_get_uid = function () {
        return window.DP_UID_COUNTER++;
      };
      const $html = angular.element(document.getElementsByTagName('html')[0]);

      angular.element().ready(() => {
        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          const module = angular.module('AdminUpdateWatcher_App');
          for (let x = 0; x < window.DP_CTRL_REG.length; x++) {
            module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        angular.bootstrap($html, ['AdminUpdateWatcher_App']);
      });
    }
  };
});
