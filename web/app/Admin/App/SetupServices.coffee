define [
	'Admin/Main/Service/SessionPing',
	'Admin/Cloud/App/CloudService'
], (
	Admin_Main_Service_SessionPing,
	Admin_Cloud_App_CloudService
) ->
	return (Module) ->
		Module.service('SessionPing', ['Api', (Api) ->
			return new Admin_Main_Service_SessionPing(Api)
		])

		Module.service('Cloud', [ ->
			return new Admin_Cloud_App_CloudService()
		])

		Module.run(['SessionPing', (SessionPing) ->
			# start pinging after 20 seconds
			window.setTimeout(->
				SessionPing.startInterval()
			, 20000)
		])

		Module.run(['$rootScope', 'dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketFilter', ($rootScope, dpObTypesDefTicketCriteria, dpObTypesDefTicketActions, dpObTypesDefTicketFilter) ->
			getAppStateName = (name) ->
				parts = name.split('.')
				while parts.length > 2
					parts.pop()
				return parts.join('.')

			$rootScope.$on('$stateChangeSuccess', (event, toState, toParams, fromState, fromParams) ->
				if not fromState or not toState
					return

				# When navigating to a new section, clear cached trigger options
				last = getAppStateName(fromState.name)
				now  = getAppStateName(toState.name)
				if last != now
					dpObTypesDefTicketCriteria.resetData()
					dpObTypesDefTicketActions.resetData()
					dpObTypesDefTicketFilter.resetData()
			)
		])