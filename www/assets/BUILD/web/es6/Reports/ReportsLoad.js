const angular = require('../../bower_components/angular/angular');

export const start = () => {
  require('./CtrlList');
  window.DP_UID_COUNTER = 0;
  window.dp_get_uid = function() {
    return window.DP_UID_COUNTER++;
  };
  const $html = angular.element(document.getElementsByTagName('html')[0]);

  // All target=blanks need to null out window.opener
  $(document).on('click', 'a[target="_blank"]', function(ev) {
    ev.preventDefault();
    const o = window.open($(this).attr('href'));
    o.opener = null;
  });

  angular.element().ready(function() {
    $html.addClass('ng-app');

    if (window.DP_CTRL_REG) {
      const module = angular.module('Reports_App');
      for (let x = 0; x < window.DP_CTRL_REG.length; x++) {
        module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
      }
    }

    angular.bootstrap($html, ['Reports_App']);
  });
};
