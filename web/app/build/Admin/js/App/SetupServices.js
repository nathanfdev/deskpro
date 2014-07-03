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
      return Module.run([
        'SessionPing', function(SessionPing) {
          return window.setTimeout(function() {
            return SessionPing.startInterval();
          }, 20000);
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupServices.js.map
