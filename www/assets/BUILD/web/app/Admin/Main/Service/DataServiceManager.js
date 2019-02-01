define([
  'DeskPRO/Util/Strings',
  'Admin/CustomFields/Tickets/DataService/TicketFields',
  'Admin/CustomFields/Chat/DataService/ChatFields',
  'Admin/CustomFields/User/DataService/UserFields',
  'Admin/CustomFields/Org/DataService/OrgFields',
  'Admin/CustomFields/Billing/DataService/BillingFields',
  'Admin/CustomFields/Kb/DataService/KbFields',
  'Admin/CustomFields/DataService/CustomFields',
  'Admin/TicketFilters/DataService/TicketFilters',
  'Admin/TicketDeps/DataService/TicketDeps',
  'Admin/ChatDeps/DataService/ChatDeps',
  'Admin/TicketEscalations/DataService/TicketEscalations',
  'Admin/TicketMacros/DataService/TicketMacros',
  'Admin/TicketSlas/DataService/TicketSlas',
  'Admin/TicketTriggers/DataService/TriggersNew',
  'Admin/TicketTriggers/DataService/TriggersReply',
  'Admin/TicketTriggers/DataService/TriggersUpdate',

  'Admin/TicketWebhooks/DataService/Webhooks',

  'Admin/TicketProblems/DataService/Problems',
  'Admin/TwitterAccounts/DataService/TwitterAccounts',
  'Admin/ApiKeys/DataService/ApiKeys',
  'Admin/ApiKeys/DataService/ApiLogs',
  'Admin/ApiKeys/DataService/ApiTags',
  'Admin/Banning/DataService/Bans',
  'Admin/UserGroups/DataService/UserGroups',
  'Admin/UserRules/DataService/UserRules',
  'Admin/Agents/DataService/Agents',
  'Admin/RoundRobin/DataService/RoundRobin',
  'Admin/AgentGroups/DataService/AgentGroups',
  'Admin/AgentTeams/DataService/AgentTeams',
  'Admin/Tasks/DataService/Tasks',
  'Admin/Usersources/DataService/Usersources',
  'Admin/Portal/DataService/PortalGeneralSettings',
  'Admin/Server/DataService/Jobs'
], function(
  Strings,
  DataService_TicketFields,
  DataService_ChatFields,
  DataService_UserFields,
  DataService_OrgFields,
  DataService_BillingFields,
  DataService_KbFields,
  DataService_CustomFields,
  DataService_TicketFilters,
  DataService_TicketDeps,
  DataService_ChatDeps,
  DataService_TicketEscalations,
  DataService_TicketMacros,
  DataService_TicketSlas,
  DataService_TriggersNew,
  DataService_TriggersReply,
  DataService_TriggersUpdate,

  DataService_Webhooks,

  DataService_Problems,
  DataService_TwitterAccounts,
  DataService_ApiKeys,
  DataService_ApiLogs,
  DataService_ApiTags,
  DataService_Bans,
  DataService_UserGroups,
  DataService_UserRules,
  DataService_Agents,
  DataService_RoundRobin,
  DataService_AgentGroups,
  DataService_AgentTeams,
  DataService_Tasks,
  DataService_Usersources,
  DataService_PortalGeneralSettings,
  DataService_Jobs
) {
  const serviceMap = {
    "DataService_TicketFields": DataService_TicketFields,
    "DataService_ChatFields": DataService_ChatFields,
    "DataService_UserFields": DataService_UserFields,
    "DataService_OrgFields": DataService_OrgFields,
    "DataService_BillingFields": DataService_BillingFields,
    "DataService_KbFields": DataService_KbFields,
    "DataService_CustomFields": DataService_CustomFields,
    "DataService_TicketFilters": DataService_TicketFilters,
    "DataService_TicketDeps": DataService_TicketDeps,
    "DataService_ChatDeps": DataService_ChatDeps,
    "DataService_TicketEscalations": DataService_TicketEscalations,
    "DataService_TicketMacros": DataService_TicketMacros,
    "DataService_TicketSlas": DataService_TicketSlas,
    "DataService_TriggersNew": DataService_TriggersNew,
    "DataService_TriggersReply": DataService_TriggersReply,
    "DataService_TriggersUpdate": DataService_TriggersUpdate,
    "DataService_Webhooks": DataService_Webhooks,
    "DataService_Problems": DataService_Problems,
    "DataService_TwitterAccounts": DataService_TwitterAccounts,
    "DataService_ApiKeys": DataService_ApiKeys,
    "DataService_ApiLogs": DataService_ApiLogs,
    "DataService_ApiTags": DataService_ApiTags,
    "DataService_Bans": DataService_Bans,
    "DataService_UserGroups": DataService_UserGroups,
    "DataService_UserRules": DataService_UserRules,
    "DataService_Agents": DataService_Agents,
    "DataService_RoundRobin": DataService_RoundRobin,
    "DataService_AgentGroups": DataService_AgentGroups,
    "DataService_AgentTeams": DataService_AgentTeams,
    "DataService_Tasks": DataService_Tasks,
    "DataService_Usersources": DataService_Usersources,
    "DataService_PortalGeneralSettings": DataService_PortalGeneralSettings,
    "DataService_Jobs": DataService_Jobs
  };

  /*
   * A simple wrapper around the data services
   */
  class Admin_Main_Service_DataServiceManager {
    constructor($injector) {
      this.$injector = $injector;
      this.ds_cache = {};
      this.registered = {};
    }

    get(serviceId, ...args) {
      let obj;
      let cacheKey = serviceId;

      cacheKey = args.reduce(
        (prev, current) => prev + '_' + current.toString(),
        cacheKey
      );

      if (this.ds_cache[cacheKey]) {
        obj = this.ds_cache[cacheKey];
      } else {
        obj = this.factory.apply(this, arguments);
        this.ds_cache[cacheKey] = obj;
      }

      return obj;
    }



    factory(serviceId) {
      // If this class has a custom initXXX method, call that
      // instead uf the default
      const initName = `init${Strings.ucFirst(Strings.toCamelCase(serviceId))}`;
      if (this[initName] != null) { return this[initName](); }

      const name = `DataService_${serviceId}`;
      const constructor = serviceMap[name];

      if (!constructor) { throw new Error(`Invalid data service name: ${name}`); }

      const obj = this.$injector.instantiate(constructor);
      if (obj.init != null) { obj.init.apply(obj, Array.prototype.slice.call(arguments, 1)); }
      return obj;
    }
  }

  return Admin_Main_Service_DataServiceManager;
});
