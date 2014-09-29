(function() {
  define(['AdminRouting', 'Admin/Cloud/App/RouteMutator'], function(AdminRouting, Admin_Cloud_App_RouteMutator) {
    return function(Module) {
      return Module.config([
        '$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
          var makeProvider, procRoute, route, routeMutator, _i, _j, _len, _len1, _ref, _results;
          $urlRouterProvider.otherwise("/");
          makeProvider = function(view) {
            return [
              'dpTemplateManager', function(dpTemplateManager) {
                return dpTemplateManager.get(view);
              }
            ];
          };
          if (window.DP_IS_CLOUD) {
            routeMutator = new Admin_Cloud_App_RouteMutator();
          }
          procRoute = function(route) {
            var id, opts, url, v, viewName;
            id = route.id;
            url = route.url;
            if (route.templateName != null) {
              route.templateProvider = makeProvider(route.templateName);
            }
            opts = {
              url: url,
              data: route.data || null
            };
            if (route.resolve) {
              opts.resolve = route.resolve;
            }
            if (route.views) {
              opts.views = route.views;
            } else {
              opts.views = {};
              v = {};
              if (route.templateProvider) {
                v.templateProvider = route.templateProvider;
              } else if (route.templateName) {
                v.templateName = route.templateName;
              }
              if (route.controller) {
                v.controller = route.controller;
              }
              if (route.target) {
                viewName = route.target;
              } else {
                if (id.split('.').length === 2) {
                  viewName = "appbody";
                } else {
                  viewName = "";
                }
              }
              opts.views[viewName] = v;
            }
            return $stateProvider.state(id, opts);
          };
          for (_i = 0, _len = AdminRouting.length; _i < _len; _i++) {
            route = AdminRouting[_i];
            if (routeMutator) {
              route = routeMutator.processRoute(route);
              if (!route) {
                continue;
              }
            }
            procRoute(route);
          }
          if (routeMutator) {
            _ref = routeMutator.getExtraRoutes();
            _results = [];
            for (_j = 0, _len1 = _ref.length; _j < _len1; _j++) {
              route = _ref[_j];
              _results.push(procRoute(route));
            }
            return _results;
          }
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupRouting.js.map
