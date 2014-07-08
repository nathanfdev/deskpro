define [
	'DeskPRO/Util/Strings',
	'Admin/CustomFields/Tickets/DataService/TicketFields',
	'Admin/CustomFields/Chat/DataService/ChatFields',
	'Admin/CustomFields/User/DataService/UserFields',
	'Admin/CustomFields/Org/DataService/OrgFields',
	'Admin/TicketFilters/DataService/TicketFilters',
	'Admin/TicketDeps/DataService/TicketDeps',
	'Admin/ChatDeps/DataService/ChatDeps',
	'Admin/TicketEscalations/DataService/TicketEscalations',
	'Admin/TicketMacros/DataService/TicketMacros',
	'Admin/TicketSlas/DataService/TicketSlas',
	'Admin/TicketTriggers/DataService/TriggersNew',
	'Admin/TicketTriggers/DataService/TriggersReply',
	'Admin/TicketTriggers/DataService/TriggersUpdate',
	'Admin/TwitterAccounts/DataService/TwitterAccounts',
	'Admin/ApiKeys/DataService/ApiKeys',
	'Admin/Banning/DataService/Bans',
	'Admin/UserGroups/DataService/UserGroups',
	'Admin/UserRules/DataService/UserRules',
	'Admin/Agents/DataService/Agents',
	'Admin/RoundRobin/DataService/RoundRobin',
	'Admin/AgentGroups/DataService/AgentGroups',
	'Admin/AgentTeams/DataService/AgentTeams',
	'Admin/Tasks/DataService/Tasks',
], (
	Strings,
	DataService_TicketFields,
	DataService_ChatFields,
	DataService_UserFields,
	DataService_OrgFields,
	DataService_TicketFilters,
	DataService_TicketDeps,
	DataService_ChatDeps,
	DataService_TicketEscalations,
	DataService_TicketMacros,
	DataService_TicketSlas,
	DataService_TriggersNew,
	DataService_TriggersReply,
	DataService_TriggersUpdate,
	DataService_TwitterAccounts,
	DataService_ApiKeys,
	DataService_Bans,
	DataService_UserGroups,
	DataService_UserRules,
	DataService_Agents,
	DataService_RoundRobin,
	DataService_AgentGroups,
	DataService_AgentTeams,
	DataService_Tasks,
) ->
	###
	# A simple wrapper around the data services
	###
	class Admin_Main_Service_DataServiceManager
		constructor: (@$injector) ->
			@ds_cache = {}
			@registered = {}

		get: (serviceId) ->
			if @ds_cache[serviceId]
				obj = @ds_cache[serviceId]
			else
				obj = null

				# If this class has a custom initXXX method, call that
				# instead uf the default
				initName = 'init' + Strings.ucFirst(Strings.toCamelCase(serviceId))
				if @[initName]?
					obj = @[initName]()

				if not obj
					name = 'DataService_' + serviceId
					eval("constructor = #{name};")

					if not constructor
						throw new Error("Invalid data service name: " + name)

					obj = @$injector.instantiate(constructor)

				@ds_cache[serviceId] = obj

			return obj