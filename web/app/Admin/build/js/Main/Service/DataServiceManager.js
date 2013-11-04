(function() {
  define(['Admin/TicketFilters/DataService/TicketFilters', 'Admin/TicketEscalations/DataService/TicketEscalations', 'Admin/TicketMacros/DataService/TicketMacros', 'Admin/TicketSlas/DataService/TicketSlas'], function(DataService_TicketFilters, DataService_TicketEscalations, DataService_TicketMacros, DataService_TicketSlas) {
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
        var name, obj;
        if (this.ds_cache[serviceId]) {
          obj = this.ds_cache[serviceId];
        } else {
          name = 'DataService_' + serviceId;
          eval("constructor = " + name + ";");
          if (!constructor) {
            throw new Error("Invalid data service name: " + name);
          }
          obj = this.$injector.instantiate(constructor);
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