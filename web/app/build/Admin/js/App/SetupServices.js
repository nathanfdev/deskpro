(function() {
  define(['Admin/Main/Service/SessionPing', 'Admin/License/Service/DpLicense', 'Admin/Cloud/App/CloudService', 'angular'], function(Admin_Main_Service_SessionPing, Admin_License_Service_DpLicense, Admin_Cloud_App_CloudService, angular) {
    return function(Module) {
      Module.service('SessionPing', [
        'Api', function(Api) {
          return new Admin_Main_Service_SessionPing(Api);
        }
      ]);
      Module.service('DpLicense', [
        'Api', '$modal', '$http', '$q', function(Api, $modal, $http, $q) {
          return new Admin_License_Service_DpLicense(Api, $modal, $http, $q);
        }
      ]);
      Module.service('Cloud', [
        function() {
          return new Admin_Cloud_App_CloudService();
        }
      ]);
      Module.run([
        'SessionPing', function(SessionPing) {
          return window.setTimeout(function() {
            return SessionPing.startInterval();
          }, 20000);
        }
      ]);
      Module.run([
        '$rootScope', 'dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketFilter', function($rootScope, dpObTypesDefTicketCriteria, dpObTypesDefTicketActions, dpObTypesDefTicketFilter) {
          var getAppStateName;
          getAppStateName = function(name) {
            var parts;
            parts = name.split('.');
            while (parts.length > 2) {
              parts.pop();
            }
            return parts.join('.');
          };
          return $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
            var last, now;
            if (!fromState || !toState) {
              return;
            }
            last = getAppStateName(fromState.name);
            now = getAppStateName(toState.name);
            if (last !== now) {
              dpObTypesDefTicketCriteria.resetData();
              dpObTypesDefTicketActions.resetData();
              return dpObTypesDefTicketFilter.resetData();
            }
          });
        }
      ]);
      return Module.filter('orderObjectBy', function() {
        return function(items, field, reverse) {
          var filtered;
          filtered = [];
          angular.forEach(items, function(item) {
            return filtered.push(item);
          });
          filtered.sort(function(a, b) {
            if (a[field] > b[field]) {
              return 1;
            } else {
              return -1;
            }
          });
          reverse && filtered.reverse();
          return filtered;
        };
      });
    };
  });

}).call(this);

//# sourceMappingURL=SetupServices.js.map
