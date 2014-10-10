(function() {
  var __slice = [].slice;

  define(['DeskPRO/Util/Strings', 'Admin/CustomFields/Tickets/DataService/TicketFields', 'Admin/CustomFields/Chat/DataService/ChatFields', 'Admin/CustomFields/User/DataService/UserFields', 'Admin/CustomFields/Org/DataService/OrgFields', 'Admin/CustomFields/DataService/CustomFields', 'Admin/TicketFilters/DataService/TicketFilters', 'Admin/TicketDeps/DataService/TicketDeps', 'Admin/ChatDeps/DataService/ChatDeps', 'Admin/TicketEscalations/DataService/TicketEscalations', 'Admin/TicketMacros/DataService/TicketMacros', 'Admin/TicketSlas/DataService/TicketSlas', 'Admin/TicketTriggers/DataService/TriggersNew', 'Admin/TicketTriggers/DataService/TriggersReply', 'Admin/TicketTriggers/DataService/TriggersUpdate', 'Admin/TwitterAccounts/DataService/TwitterAccounts', 'Admin/ApiKeys/DataService/ApiKeys', 'Admin/Banning/DataService/Bans', 'Admin/UserGroups/DataService/UserGroups', 'Admin/UserRules/DataService/UserRules', 'Admin/Agents/DataService/Agents', 'Admin/RoundRobin/DataService/RoundRobin', 'Admin/AgentGroups/DataService/AgentGroups', 'Admin/AgentTeams/DataService/AgentTeams', 'Admin/Tasks/DataService/Tasks', 'Admin/Labels/DataService/Settings'], function(Strings, DataService_TicketFields, DataService_ChatFields, DataService_UserFields, DataService_OrgFields, DataService_CustomFields, DataService_TicketFilters, DataService_TicketDeps, DataService_ChatDeps, DataService_TicketEscalations, DataService_TicketMacros, DataService_TicketSlas, DataService_TriggersNew, DataService_TriggersReply, DataService_TriggersUpdate, DataService_TwitterAccounts, DataService_ApiKeys, DataService_Bans, DataService_UserGroups, DataService_UserRules, DataService_Agents, DataService_RoundRobin, DataService_AgentGroups, DataService_AgentTeams, DataService_Tasks, DataService_LabelSettings) {

    /*
    	 * A simple wrapper around the data services
     */
    var Admin_Main_Service_DataServiceManager;
    return Admin_Main_Service_DataServiceManager = (function() {
      function Admin_Main_Service_DataServiceManager($injector) {
        this.$injector = $injector;
        this.ds_cache = {};
        this.registered = {};
      }

      Admin_Main_Service_DataServiceManager.prototype.get = function() {
        var args, cacheKey, obj, serviceId;
        serviceId = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
        cacheKey = serviceId;
        cacheKey = args.reduce(function(prev, current) {
          return prev + '_' + current.toString();
        }, cacheKey);
        if (this.ds_cache[cacheKey]) {
          obj = this.ds_cache[cacheKey];
        } else {
          obj = this.factory.apply(this, arguments);
          this.ds_cache[cacheKey] = obj;
        }
        return obj;
      };

      Admin_Main_Service_DataServiceManager.prototype.factory = function(serviceId) {
        var initName, name, obj;
        initName = 'init' + Strings.ucFirst(Strings.toCamelCase(serviceId));
        if (this[initName] != null) {
          return this[initName]();
        }
        name = 'DataService_' + serviceId;
        eval("constructor = " + name + ";");
        if (!constructor) {
          throw new Error("Invalid data service name: " + name);
        }
        obj = this.$injector.instantiate(constructor);
        if (obj.init != null) {
          obj.init.apply(obj, Array.prototype.slice.call(arguments, 1));
        }
        return obj;
      };

      return Admin_Main_Service_DataServiceManager;

    })();
  });

}).call(this);

//# sourceMappingURL=DataServiceManager.js.map
