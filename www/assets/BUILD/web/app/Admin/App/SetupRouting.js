define([
  'AdminRouting',
  'Admin/Cloud/App/RouteMutator'
], (
  AdminRouting,
  Admin_Cloud_App_RouteMutator
) =>
  Module =>
    Module.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider) {
      let routeMutator;
      $urlRouterProvider.otherwise('/');

      // Load templates through the dpTemplateManager
      // so we can take advantage of our preloading scheme
      const makeProvider = view =>
        ['dpTemplateManager', dpTemplateManager => dpTemplateManager.get(view)
        ]
      ;

      if (window.DP_IS_CLOUD) {
        routeMutator = new Admin_Cloud_App_RouteMutator();
      }

      const procRoute = function (route) {
        const { id } = route;
        const { url } = route;

        if (route.templateName != null) {
          route.templateProvider = makeProvider(route.templateName);
        }

        const opts = {
          url,
          data: route.data || null
        };

        if (route.resolve) {
          opts.resolve = route.resolve;
        }

        if (route.views) {
          opts.views = route.views;
        } else if (route.abstract) {
          opts.abstract = true;
          opts.template = '<ui-view/>';
        } else {
          let viewName;
          opts.views = {};

          const v = {};
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
            // An app-level (tickets.ticket_deps)
            // Is always added to the appbody
            if (id.split('.').length === 2) {
              viewName = 'appbody';
            } else {
              viewName = '';
            }
          }

          opts.views[viewName] = v;
        }

        return $stateProvider.state(id, opts);
      };

      for (var route of Array.from(AdminRouting)) {
        if (routeMutator) {
          route = routeMutator.processRoute(route);
          if (!route) { continue; }
        }

        procRoute(route);
      }

      if (routeMutator) {
        return (() => {
          const result = [];
          for (route of Array.from(routeMutator.getExtraRoutes())) {
            result.push(procRoute(route));
          }
          return result;
        })();
      }
    }
    ])

);
