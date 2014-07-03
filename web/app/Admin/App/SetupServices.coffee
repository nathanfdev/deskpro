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