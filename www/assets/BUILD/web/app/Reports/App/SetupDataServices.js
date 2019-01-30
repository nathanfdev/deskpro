// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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