(function() {
  define(['angular', 'Admin/App/AdminModule', 'Admin/App/SetupDataServices', 'Admin/App/SetupDirectives', 'DeskPRO/App/SetupLogging', 'DeskPRO/App/SetupNetwork', 'Admin/App/SetupRouting', 'DeskPRO/App/SetupServices', 'Admin/App/SetupServices', 'Admin/App/SetupTemplates'], function(angular, AdminModule, SetupDataServices, SetupDirectives, SetupLogging, SetupNetwork, SetupRouting, SetupServices, AdminSetupServices, SetupTemplates) {
    var _ref, _ref1;
    SetupServices(AdminModule);
    AdminSetupServices(AdminModule);
    SetupLogging(AdminModule);
    SetupDataServices(AdminModule);
    SetupNetwork(AdminModule);
    SetupDirectives(AdminModule);
    SetupRouting(AdminModule);
    SetupTemplates(AdminModule);
    if ((_ref = window.parent) != null ? _ref.DP_FRAME_OVERLAY_admin : void 0) {
      if ((_ref1 = window.parent) != null) {
        _ref1.DP_FRAME_OVERLAY_admin.callLoaded();
      }
    }
    return AdminModule;
  });

}).call(this);

//# sourceMappingURL=App.js.map
