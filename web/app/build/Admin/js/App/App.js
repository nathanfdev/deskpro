(function() {
  define(['angular', 'Admin/App/AdminModule', 'Admin/App/SetupDataServices', 'Admin/App/SetupDirectives', 'DeskPRO/App/SetupLogging', 'DeskPRO/App/SetupNetwork', 'Admin/App/SetupRouting', 'DeskPRO/App/SetupServices', 'Admin/App/SetupServices', 'Admin/App/SetupTemplates'], function(angular, AdminModule, SetupDataServices, SetupDirectives, SetupLogging, SetupNetwork, SetupRouting, SetupServices, AdminSetupServices, SetupTemplates) {
    var _ref, _ref1;
    SetupServices(AdminModule);
    AdminSetupServices(AdminModule);
    SetupLogging(AdminModule);
    SetupDataServices(AdminModule);
    AdminModule.factory('dpHttpSessionInterceptor', [
      '$q', function($q) {
        return {
          responseError: function(rejection) {
            var _ref;
            if ((rejection.status != null) && (((_ref = rejection.data) != null ? _ref.error : void 0) != null) && rejection.status === 403 && rejection.data.error === "session_expired") {
              return window.location = window.DP_BASE_URL + 'agent/login?timeout=1&return=' + encodeURIComponent(window.DP_BASE_URL + 'admin/' + window.location.hash);
            } else {
              return $q.reject(rejection);
            }
          }
        };
      }
    ]);
    AdminModule.config([
      '$httpProvider', function($httpProvider) {
        return $httpProvider.interceptors.push('dpHttpSessionInterceptor');
      }
    ]);
    AdminModule.constant('angularMomentConfig', {
      timezone: window.DP_PERSON_TZ
    });
    SetupNetwork(AdminModule);
    SetupDirectives(AdminModule);
    SetupRouting(AdminModule);
    SetupTemplates(AdminModule);
    if (window.DP_REDIRECT_TO_LICENSE) {
      console.log("Redirect to license");
      window.location.hash = '/license';
    }
    if ((_ref = window.parent) != null ? (_ref1 = _ref.DP_FRAME_OVERLAYS) != null ? _ref1.admin : void 0 : void 0) {
      window.parent.DP_FRAME_OVERLAYS.admin.callLoaded();
      AdminModule.run([
        '$rootScope', function($rootScope) {
          if (!window.DP_REDIRECT_TO_LICENSE) {
            return $rootScope.$on('$stateChangeSuccess', function() {
              return window.parent.DP_FRAME_OVERLAYS.admin.setHash(window.location.hash);
            });
          }
        }
      ]);
    }
    return AdminModule;
  });

}).call(this);

//# sourceMappingURL=App.js.map
