/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Service/SessionPing',
  'Admin/Main/Service/DpDate',
  'Admin/Main/Service/LangSyncApi',
  'Admin/License/Service/DpLicense',
  'Admin/Cloud/App/CloudService',
  'angular'
], (
  Admin_Main_Service_SessionPing,
  Admin_Main_Service_DpDate,
  Admin_Main_Service_LangSyncApi,
  Admin_License_Service_DpLicense,
  Admin_Cloud_App_CloudService,
  angular
) =>
  function(Module) {
    Module.service('SessionPing', ['Api', Api => new Admin_Main_Service_SessionPing(Api)
    ]);

    Module.service('DpLicense', ['Api', '$modal', '$http', '$q', (Api, $modal, $http, $q) => new Admin_License_Service_DpLicense(Api, $modal, $http, $q)
    ]);

    Module.service('DpDateService', Admin_Main_Service_DpDate);

    Module.service('Cloud', [ () => new Admin_Cloud_App_CloudService()
    ]);

    Module.service('LangSyncApi', ['$http', 'Growl', ($http, Growl) =>
      new Admin_Main_Service_LangSyncApi(
        $http,
        window.DP_LANGUAGE_SYNC_API,
        Growl
      )
    
    ]);

    Module.run(['SessionPing', SessionPing =>
      // start pinging after 20 seconds
      window.setTimeout(() => SessionPing.startInterval()
      , 20000)
    
    ]);

    Module.run(['$rootScope', 'dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketFilter', function($rootScope, dpObTypesDefTicketCriteria, dpObTypesDefTicketActions, dpObTypesDefTicketFilter) {
      const getAppStateName = function(name) {
        const parts = name.split('.');
        while (parts.length > 2) {
          parts.pop();
        }
        return parts.join('.');
      };

      return $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
        if (!fromState || !toState) {
          return;
        }

        // When navigating to a new section, clear cached trigger options
        const last = getAppStateName(fromState.name);
        const now  = getAppStateName(toState.name);
        if (last !== now) {
          dpObTypesDefTicketCriteria.resetData();
          dpObTypesDefTicketActions.resetData();
          return dpObTypesDefTicketFilter.resetData();
        }
      });
    }
    ]);

    return Module.filter('orderObjectBy', () =>
      function(items, field, reverse) {
        const filtered = [];
        angular.forEach(items, item => filtered.push(item));

        filtered.sort(function(a, b) {
          if (a[field] > b[field]) { return 1; } else { return -1; }
        });

        reverse && filtered.reverse();
        return filtered;
      }
    );
  }
);
