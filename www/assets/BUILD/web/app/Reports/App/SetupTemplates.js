// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Reports/Main/Service/TemplateManager',
  'ReportsRouting'
], (
  Reports_Main_Service_TemplateManager,
  ReportsRouting
) =>
  function(Module) {
    Module.service('dpTemplateManager', ['$templateCache', '$http', '$q', ($templateCache, $http, $q) => new Reports_Main_Service_TemplateManager($templateCache, $http, $q)
    ]);

    // Decorate the $templateCache so view names are always the 'short' names
    // and not URLs
    // e.g.  /deskpro/reports/load-view/Index/blank.html -> Index/blank.html
    Module.config(['$provide', $provide =>
      $provide.decorator('$templateCache', ['$delegate', function($delegate) {
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
      ])
    
    ]);

    // Preload templates
    return Module.run(['dpTemplateManager', function(dpTemplateManager) {
      const templates = [
        'Index/modal-alert.html',
        'Index/modal-confirm.html',
        'Index/modal-confirm-leavetab.html',
        'Index/blank.html',
      ];

      for (let _x of Object.keys(ReportsRouting || {})) {
        const route = ReportsRouting[_x];
        if (route.templateName) {
          templates.push(route.templateName);
        }
      }

      for (let t of Array.from(templates)) {
        dpTemplateManager.load(t);
      }

      return dpTemplateManager.loadPending().then(() =>
        window.setTimeout(() => window.DP_IS_BOOTED = true
        , 400)
      );
    }
    ]);
  }
);
