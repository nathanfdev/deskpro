(function() {
  define(['AdminRouting'], function(AdminRouting) {
    return function(Module) {
      return Module.config([
        '$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
          var id, makeProvider, opts, route, url, v, viewName, _i, _len, _results;
          $urlRouterProvider.otherwise("/");
          makeProvider = function(view) {
            return [
              'dpTemplateManager', function(dpTemplateManager) {
                return dpTemplateManager.get(view);
              }
            ];
          };
          _results = [];
          for (_i = 0, _len = AdminRouting.length; _i < _len; _i++) {
            route = AdminRouting[_i];
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
            _results.push($stateProvider.state(id, opts));
          }
          return _results;
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupRouting.js.map
