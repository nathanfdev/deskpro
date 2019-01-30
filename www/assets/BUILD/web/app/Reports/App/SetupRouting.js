/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'ReportsRouting',
], (
  ReportsRouting
) =>
  Module =>
    Module.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
      $urlRouterProvider.otherwise("/");

      // Load templates through the dpTemplateManager
      // so we can take advantage of our preloading scheme
      const makeProvider = view =>
        ['dpTemplateManager', dpTemplateManager => dpTemplateManager.get(view)
        ]
      ;

      return (() => {
        const result = [];
        for (let route of Array.from(ReportsRouting)) {
          const { id } = route;
          const { url } = route;

          if (route.templateName != null) {
            route.templateProvider = makeProvider(route.templateName);
          }

          const opts = {
            url,
            data: route.data || null
          };

          if (route.views) {
            opts.views = route.views;
          } else {
            var viewName;
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
                viewName = "rightpane";
              } else {
                viewName = "";
              }
            }

            opts.views[viewName] = v;
          }

          result.push($stateProvider.state(id, opts));
        }
        return result;
      })();
    }
    ])
  
);