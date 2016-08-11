const angular = require('../../bower_components/angular/angular');

export class AdminLoad {
  /**
   * ace editor hotfix
   * see https://github.com/angular-ui/ui-ace/issues/104
   * @type {Function}
   */
  constructor() {
    const old       = window.ace.edit;
    window.ace.edit = () => {
      const instance           = old.apply(old, arguments);
      instance.$blockScrolling = Infinity;
      return instance;
    };

    if (!window.console) {
      window.console = {
        log:   () => {
        },
        warn:  () => {
        },
        error: () => {
        }
      };
    }
  };

}

export const start = () => {
  require('../../bower_components/spectrum/spectrum');
  require('./CtrlList');

  let loadingEl = document.getElementById('dp_loading');
  loadingEl.parentNode.removeChild(loadingEl);
  loadingEl = null;

  window.DP_UID_COUNTER = 0;
  window.dp_get_uid     = () => window.DP_UID_COUNTER++;

  const $html = angular.element(document.getElementsByTagName('html')[0]);

  // All target=_blanks need to null out window.opener
  // to prevent malicious third-parties from trying to redirect us
  $(document).on('click', 'a[target="_blank"]', (ev) => {
    ev.preventDefault();
    var o    = window.open($(ev.target).attr('href'));
    o.opener = null;
  });


  angular.element().ready(() => {
    $html.addClass('ng-app');

    if (window.DP_CTRL_REG) {
      const module = angular.module('Admin_App');
      for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
        module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
      }
    }

    angular.bootstrap($html, ['Admin_App']);
  });
};
