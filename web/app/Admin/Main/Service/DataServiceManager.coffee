define [
	'Admin/TicketFilters/DataService/TicketFilters',
	'Admin/TicketEscalations/DataService/TicketEscalations',
	'Admin/TicketMacros/DataService/TicketMacros',
	'Admin/TicketSlas/DataService/TicketSlas'
], (
	DataService_TicketFilters,
	DataService_TicketEscalations,
	DataService_TicketMacros,
	DataService_TicketSlas
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
				name = 'DataService_' + serviceId
				eval("constructor = #{name};")

				if not constructor
					throw new Error("Invalid data service name: " + name)

				obj = @$injector.instantiate(constructor)
				@ds_cache[serviceId] = obj

			return obj