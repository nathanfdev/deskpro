(function() {
  define(['angular', 'Reports/App/ReportsModule', 'Reports/App/SetupDataServices', 'Reports/App/SetupDirectives', 'DeskPRO/App/SetupLogging', 'DeskPRO/App/SetupNetwork', 'Reports/App/SetupRouting', 'DeskPRO/App/SetupServices', 'Reports/App/SetupTemplates', 'Reports/Main/Service/SessionPing'], function(angular, ReportsModule, SetupDataServices, SetupDirectives, SetupLogging, SetupNetwork, SetupRouting, SetupServices, SetupTemplates, Reports_Main_Service_SessionPing) {
    var _ref, _ref1;
    SetupServices(ReportsModule);
    SetupLogging(ReportsModule);
    SetupDataServices(ReportsModule);
    SetupNetwork(ReportsModule);
    SetupDirectives(ReportsModule);
    SetupRouting(ReportsModule);
    SetupTemplates(ReportsModule);
    ReportsModule.factory('dpHttpSessionInterceptor', [
      '$q', function($q) {
        return {
          responseError: function(rejection) {
            var _ref;
            if ((rejection.status != null) && (((_ref = rejection.data) != null ? _ref.error : void 0) != null) && rejection.status === 403 && rejection.data.error === "session_expired") {
              return window.location = window.DP_BASE_URL + 'agent/login?timeout=1&return=' + encodeURIComponent(window.DP_BASE_URL + 'reports/' + window.location.hash);
            } else {
              return $q.reject(rejection);
            }
          }
        };
      }
    ]);
    ReportsModule.config([
      '$httpProvider', function($httpProvider) {
        return $httpProvider.interceptors.push('dpHttpSessionInterceptor');
      }
    ]);
    ReportsModule.service('SessionPing', [
      'Api', function(Api) {
        return new Reports_Main_Service_SessionPing(Api);
      }
    ]);
    ReportsModule.run([
      'SessionPing', function(SessionPing) {
        return window.setTimeout(function() {
          return SessionPing.startInterval();
        }, 20000);
      }
    ]);
    if ((_ref = window.parent) != null ? (_ref1 = _ref.DP_FRAME_OVERLAYS) != null ? _ref1.reports : void 0 : void 0) {
      window.parent.DP_FRAME_OVERLAYS.reports.callLoaded();
      ReportsModule.run([
        '$rootScope', function($rootScope) {
          return $rootScope.$on('$stateChangeSuccess', function() {
            return window.parent.DP_FRAME_OVERLAYS.reports.setHash(window.location.hash);
          });
        }
      ]);
    }
    return ReportsModule;
  });

}).call(this);

//# sourceMappingURL=App.js.map
