define [
	'DeskPRO/Util/Strings',
	'Admin/CustomFields/Tickets/DataService/TicketFields',
	'Admin/CustomFields/Chat/DataService/ChatFields',
	'Admin/CustomFields/User/DataService/UserFields',
	'Admin/CustomFields/Org/DataService/OrgFields',
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
	'Admin/Labels/DataService/Settings'
	'Admin/Usersources/DataService/Usersources'
], (
	Strings,
	DataService_TicketFields,
	DataService_ChatFields,
	DataService_UserFields,
	DataService_OrgFields,
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
	DataService_LabelSettings,
	DataService_Usersources
) ->
	###
	# A simple wrapper around the data services
	###
	class Admin_Main_Service_DataServiceManager
		constructor: (@$injector) ->
			@ds_cache = {}
			@registered = {}



		get: (serviceId, args...) ->
			cacheKey = serviceId

			cacheKey = args.reduce(
				(prev, current) -> prev + '_' + current.toString()
				cacheKey
			)

			if @ds_cache[cacheKey]
				obj = @ds_cache[cacheKey]
			else
				obj = @factory.apply @, arguments
				@ds_cache[cacheKey] = obj

			obj



		factory: (serviceId) ->
			# If this class has a custom initXXX method, call that
			# instead uf the default
			initName = 'init' + Strings.ucFirst(Strings.toCamelCase(serviceId))
			return @[initName]() if @[initName]?

			name = 'DataService_' + serviceId
			eval("constructor = #{name};")

			throw new Error("Invalid data service name: " + name) if !constructor

			obj = @$injector.instantiate(constructor)
			obj.init.apply obj, Array.prototype.slice.call(arguments, 1) if obj.init?
			obj
