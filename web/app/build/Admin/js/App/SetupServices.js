(function() {
  define(['Admin/Main/Service/SessionPing', 'Admin/Cloud/App/CloudService'], function(Admin_Main_Service_SessionPing, Admin_Cloud_App_CloudService) {
    return function(Module) {
      Module.service('SessionPing', [
        'Api', function(Api) {
          return new Admin_Main_Service_SessionPing(Api);
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
      return Module.run([
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
    };
  });

}).call(this);

//# sourceMappingURL=SetupServices.js.map
