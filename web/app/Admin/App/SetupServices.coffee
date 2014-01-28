define [
	'Admin/Main/Service/SessionPing'
], (
	Admin_Main_Service_SessionPing
) ->
	return (Module) ->
		Module.service('SessionPing', ['Api', (Api) ->
			return new Admin_Main_Service_SessionPing(Api)
		])

		Module.run(['SessionPing', (SessionPing) ->
			# start pinging after 20 seconds
			window.setTimeout(->
				SessionPing.startInterval()
			, 20000)
		])