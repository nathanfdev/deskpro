(function() {
  define(['Admin/Main/Service/TemplateManager', 'AdminRouting'], function(Admin_Main_Service_TemplateManager, AdminRouting) {
    return function(Module) {
      Module.service('dpTemplateManager', [
        '$templateCache', '$http', '$q', function($templateCache, $http, $q) {
          return new Admin_Main_Service_TemplateManager($templateCache, $http, $q);
        }
      ]);
      Module.config([
        '$provide', function($provide) {
          return $provide.decorator('$templateCache', [
            '$delegate', function($delegate) {
              $delegate.ngGet = $delegate.get;
              $delegate.get = function(view) {
                view = view.replace(/^.*?\/admin\/load\-view\//g, '');
                return $delegate.ngGet(view);
              };
              $delegate.ngPut = $delegate.put;
              $delegate.put = function(view, value) {
                view = view.replace(/^.*?\/admin\/load\-view\//g, '');
                return $delegate.ngPut(view, value);
              };
              return $delegate;
            }
          ]);
        }
      ]);
      return Module.run([
        'dpTemplateManager', function(dpTemplateManager) {
          var t, templates, _i, _len;
          templates = ['Index/app-nav-setup.html', 'Index/app-nav-agents.html', 'Index/app-nav-tickets.html', 'Index/app-nav-crm.html', 'Index/app-nav-portal.html', 'Index/app-nav-chat.html', 'Index/app-nav-twitter.html', 'Index/app-nav-apps.html', 'Index/app-nav-server.html', 'Index/modal-alert.html', 'Index/modal-confirm-leavetab.html', 'Languages/modal-translate-phrase.html', 'Index/blank.html', 'Index/home.html', 'Common/work-hours-directive.html'];
          for (_i = 0, _len = templates.length; _i < _len; _i++) {
            t = templates[_i];
            dpTemplateManager.load(t);
          }
          return dpTemplateManager.loadPending().then(function() {
            return window.setTimeout(function() {
              return window.DP_IS_BOOTED = true;
            }, 400);
          });
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupTemplates.js.map
