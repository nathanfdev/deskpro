(function() {
  define(['Admin/Main/DataService/EntityManager'], function(Admin_Main_DataService_EntityManager) {
    return function(Module) {
      return Module.service('em', [
        function() {
          return new Admin_Main_DataService_EntityManager();
        }
      ]);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupDataServices.js.map
*/