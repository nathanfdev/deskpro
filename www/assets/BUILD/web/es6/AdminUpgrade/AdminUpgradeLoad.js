const angular = require('../../bower_components/angular/angular');

export const start = () => {
  require('../../bower_components/angular-route/angular-route.min');
  require('../../compiled/AdminUpgrade/App/App');
  require('../../compiled/AdminUpgrade/Main/Ctrl/UpgradeHome');
  require('../../compiled/AdminUpgrade/Main/Ctrl/UpgradeWatch');
  window.DP_UID_COUNTER = 0;
  window.dp_get_uid     = function() {
    return window.DP_UID_COUNTER++;
  };

  const $html = angular.element(document.getElementsByTagName('html')[0]);

  angular.element().ready(function() {
    $html.addClass('ng-app');

    if (window.DP_CTRL_REG) {
      const module = angular.module('AdminUpgrade_App');
      for (let x = 0; x < window.DP_CTRL_REG.length; x++) {
        module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
      }
    }

    angular.bootstrap($html, ['AdminUpgrade_App']);
  });
};
