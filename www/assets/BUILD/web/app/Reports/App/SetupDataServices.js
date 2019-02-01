define([
  'Admin/Main/DataService/EntityManager',
  'Reports/Main/Service/DataServiceManager',
], (
  Admin_Main_DataService_EntityManager,
  Reports_Main_Service_DataServiceManager
) =>
  function(Module) {

    Module.service('em', [ () => new Admin_Main_DataService_EntityManager()
    ]);

    return Module.factory('DataService', [ '$injector', $injector => new Reports_Main_Service_DataServiceManager($injector)
    ]);
  }
);