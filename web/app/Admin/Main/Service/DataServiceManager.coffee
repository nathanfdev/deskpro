define [
	'DeskPRO/Util/Strings',
	'Admin/CustomFields/Tickets/DataService/TicketFields',
	'Admin/TicketFilters/DataService/TicketFilters',
	'Admin/TicketDeps/DataService/TicketDeps',
	'Admin/TicketEscalations/DataService/TicketEscalations',
	'Admin/TicketMacros/DataService/TicketMacros',
	'Admin/TicketSlas/DataService/TicketSlas',
	'Admin/TicketTriggers/DataService/TriggersNew',
	'Admin/TicketTriggers/DataService/TriggersReply',
	'Admin/TicketTriggers/DataService/TriggersUpdate',
	'Admin/TwitterAccounts/DataService/TwitterAccounts',
], (
	Strings,
	DataService_TicketFields,
	DataService_TicketFilters,
	DataService_TicketDeps,
	DataService_TicketEscalations,
	DataService_TicketMacros,
	DataService_TicketSlas,
	DataService_TriggersNew,
	DataService_TriggersReply,
	DataService_TriggersUpdate,
	DataService_TwitterAccounts,
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