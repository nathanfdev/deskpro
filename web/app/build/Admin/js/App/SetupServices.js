(function() {
  define(['Admin/Main/Service/SessionPing'], function(Admin_Main_Service_SessionPing) {
    return function(Module) {
      Module.service('SessionPing', [
        'Api', function(Api) {
          return new Admin_Main_Service_SessionPing(Api);
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
