(function() {
  define(['DeskPRO/Util/Strings', 'Admin/CustomFields/Tickets/DataService/TicketFields', 'Admin/TicketFilters/DataService/TicketFilters', 'Admin/TicketDeps/DataService/TicketDeps', 'Admin/TicketEscalations/DataService/TicketEscalations', 'Admin/TicketMacros/DataService/TicketMacros', 'Admin/TicketSlas/DataService/TicketSlas', 'Admin/TicketTriggers/DataService/TriggersNew', 'Admin/TicketTriggers/DataService/TriggersReply', 'Admin/TicketTriggers/DataService/TriggersUpdate', 'Admin/TwitterAccounts/DataService/TwitterAccounts'], function(Strings, DataService_TicketFields, DataService_TicketFilters, DataService_TicketDeps, DataService_TicketEscalations, DataService_TicketMacros, DataService_TicketSlas, DataService_TriggersNew, DataService_TriggersReply, DataService_TriggersUpdate, DataService_TwitterAccounts) {
    /*
    	# A simple wrapper around the data services
    */

    var Admin_Main_Service_DataServiceManager;
    return Admin_Main_Service_DataServiceManager = (function() {
      function Admin_Main_Service_DataServiceManager($injector) {
        this.$injector = $injector;
        this.ds_cache = {};
        this.registered = {};
      }

      Admin_Main_Service_DataServiceManager.prototype.get = function(serviceId) {
        var initName, name, obj;
        if (this.ds_cache[serviceId]) {
          obj = this.ds_cache[serviceId];
        } else {
          obj = null;
          initName = 'init' + Strings.ucFirst(Strings.toCamelCase(serviceId));
          if (this[initName] != null) {
            obj = this[initName]();
          }
          if (!obj) {
            name = 'DataService_' + serviceId;
            eval("constructor = " + name + ";");
            if (!constructor) {
              throw new Error("Invalid data service name: " + name);
            }
            obj = this.$injector.instantiate(constructor);
          }
          this.ds_cache[serviceId] = obj;
        }
        return obj;
      };

      return Admin_Main_Service_DataServiceManager;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=DataServiceManager.js.map
*/