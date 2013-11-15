(function() {
  var __hasProp = {}.hasOwnProperty;

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
          var route, t, templates, _, _i, _len, _results;
          templates = ['Index/app-nav-setup.html', 'Index/app-nav-agents.html', 'Index/app-nav-tickets.html', 'Index/app-nav-crm.html', 'Index/app-nav-portal.html', 'Index/app-nav-chat.html', 'Index/app-nav-twitter.html', 'Index/app-nav-apps.html', 'Index/app-nav-server.html', 'Index/modal-alert.html', 'Index/modal-confirm-leavetab.html', 'Languages/modal-translate-phrase.html', 'TicketDeps/code-phpapi.html', 'TicketDeps/code-link.html', 'TicketDeps/code-win.html', 'TicketDeps/code-embed.html', 'Index/blank.html', 'Common/work-hours-directive.html', 'TicketDeps/layout-editor.html', 'TicketDeps/layout-editor-field.html'];
          for (_ in AdminRouting) {
            if (!__hasProp.call(AdminRouting, _)) continue;
            route = AdminRouting[_];
            if ((route.page != null) && route.page.templateName) {
              templates.push(route.page.templateName);
            }
            if ((route.nav != null) && route.nav.templateName) {
              templates.push(route.nav.templateName);
            }
            if ((route.list != null) && route.list.templateName) {
              templates.push(route.list.templateName);
            }
          }
          _results = [];
          for (_i = 0, _len = templates.length; _i < _len; _i++) {
            t = templates[_i];
            dpTemplateManager.load(t);
            _results.push(dpTemplateManager.loadPending().then(function() {
              return window.DP_IS_BOOTED = true;
            }));
          }
          return _results;
        }
      ]);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupTemplates.js.map
*/