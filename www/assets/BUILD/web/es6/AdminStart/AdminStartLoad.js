const angular = require('../../bower_components/angular/angular');

export const start = () => {
  require('../../vendor/detect_timezone');
  require('../../compiled/AdminStart/App/App');
  require('../../compiled/AdminStart/Ctrl/StartBase');
  require('../../compiled/AdminStart/Ctrl/Home');
  require('../../compiled/AdminStart/Ctrl/Email');
  require('../../compiled/AdminStart/Ctrl/Finish');

  window.DP_UID_COUNTER = 0;
  window.dp_get_uid     = function() {
    return window.DP_UID_COUNTER++;
  };

  const $html = angular.element(document.getElementsByTagName('html')[0]);

  angular.element().ready(function() {
    $html.addClass('ng-app');

    if (window.DP_CTRL_REG) {
      const module = angular.module('AdminStart_App');
      for (let x = 0; x < window.DP_CTRL_REG.length; x++) {
        module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
      }
    }

    angular.bootstrap($html, ['AdminStart_App']);
  });
};
