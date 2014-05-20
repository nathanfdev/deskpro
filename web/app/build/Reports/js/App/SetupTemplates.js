(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['Reports/Main/Service/TemplateManager', 'ReportsRouting'], function(Reports_Main_Service_TemplateManager, ReportsRouting) {
    return function(Module) {
      Module.service('dpTemplateManager', [
        '$templateCache', '$http', '$q', function($templateCache, $http, $q) {
          return new Reports_Main_Service_TemplateManager($templateCache, $http, $q);
        }
      ]);
      Module.config([
        '$provide', function($provide) {
          return $provide.decorator('$templateCache', [
            '$delegate', function($delegate) {
              $delegate.ngGet = $delegate.get;
              $delegate.get = function(view) {
                view = view.replace(/^.*?\/reports\/load\-view\//g, '');
                return $delegate.ngGet(view);
              };
              $delegate.ngPut = $delegate.put;
              $delegate.put = function(view, value) {
                view = view.replace(/^.*?\/reports\/load\-view\//g, '');
                return $delegate.ngPut(view, value);
              };
              return $delegate;
            }
          ]);
        }
      ]);
      return Module.run([
        'dpTemplateManager', function(dpTemplateManager) {
          var route, t, templates, _, _i, _len;
          templates = ['Index/modal-alert.html', 'Index/modal-confirm-leavetab.html', 'Index/blank.html'];
          for (_ in ReportsRouting) {
            if (!__hasProp.call(ReportsRouting, _)) continue;
            route = ReportsRouting[_];
            if (route.templateName) {
              templates.push(route.templateName);
            }
          }
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
