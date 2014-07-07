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
