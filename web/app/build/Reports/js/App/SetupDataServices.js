(function() {
  define(['Admin/Main/DataService/EntityManager', 'Reports/Main/Service/DataServiceManager'], function(Admin_Main_DataService_EntityManager, Reports_Main_Service_DataServiceManager) {
    return function(Module) {
      Module.service('em', [
        function() {
          return new Admin_Main_DataService_EntityManager();
        }
      ]);
      return Module.factory('DataService', [
        '$injector', function($injector) {
          return new Reports_Main_Service_DataServiceManager($injector);
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupDataServices.js.map
