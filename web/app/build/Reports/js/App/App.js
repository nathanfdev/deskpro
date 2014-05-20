(function() {
  define(['angular', 'Reports/App/ReportsModule', 'Reports/App/SetupDataServices', 'Reports/App/SetupDirectives', 'DeskPRO/App/SetupLogging', 'DeskPRO/App/SetupNetwork', 'Reports/App/SetupRouting', 'DeskPRO/App/SetupServices', 'Reports/App/SetupTemplates'], function(angular, ReportsModule, SetupDataServices, SetupDirectives, SetupLogging, SetupNetwork, SetupRouting, SetupServices, SetupTemplates) {
    var _ref, _ref1;
    SetupServices(ReportsModule);
    SetupLogging(ReportsModule);
    SetupDataServices(ReportsModule);
    SetupNetwork(ReportsModule);
    SetupDirectives(ReportsModule);
    SetupRouting(ReportsModule);
    SetupTemplates(ReportsModule);
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
