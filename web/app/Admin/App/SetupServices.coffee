define [
	'Admin/Main/Service/AppState',
	'Admin/Main/Service/DpApi',
	'Admin/Main/Service/Growl',
	'Admin/Main/Service/InhelpState',
], (
	Admin_Main_Service_AppState,
	Admin_Main_Service_DpApi,
	Admin_Main_Service_Growl,
	Admin_Main_Service_InhelpState,
) ->
	return (Module) ->
		Module.service('AppState', ['$rootScope', '$state', ($rootScope, $state) ->
			return new Admin_Main_Service_AppState($rootScope, $state)
		])

		Module.service('Api', ['$http', ($http) ->
			return new Admin_Main_Service_DpApi(
				$http,
				window.DP_BASE_API_URL,
				window.DP_API_TOKEN
			)
		])

		Module.service('InhelpState', ['Api', (Api) ->
			return new Admin_Main_Service_InhelpState(Api)
		])

		Module.service('Growl', [ ->
			return new Admin_Main_Service_Growl()
		])

		Module.filter('escape_url', [ ->
			return (text) ->
				return encodeURIComponent(text)
		])

		# Add logging to digest loop
		Module.config(['$provide', ($provide) ->

			# This var is used in browser tests so we can
			# properly wait for a page to be finished rendering
			window.DP_DIGEST_RUNNING = false
			subTimout = null
			startRunning = ->
				window.DP_DIGEST_RUNNING = true
				if subTimout
					clearTimeout(subTimout)
					subTimout = null

			stopRunning = ->
				if not subTimout
					subTimout = setTimeout(->
						subTimout = null
						window.DP_DIGEST_RUNNING = false
					, 100)

			$provide.decorator('$rootScope', ['dpInterfaceTimer', '$delegate', (dpInterfaceTimer, $delegate) ->
				origDigest = $delegate.$digest
				$delegate.$digest = ->
					startRunning()
					dpInterfaceTimer.startDigest()
					ret = origDigest.apply($delegate, arguments)
					dpInterfaceTimer.endDigest()
					stopRunning()
					return ret

				return $delegate
			])
		])

		# Add fcall() to $q service (like Kris Kowal's Q: https://github.com/kriskowal/q)
		# Add isPromise
		Module.config(['$provide', ($provide) ->
			$provide.decorator('$q', ['$delegate', ($delegate) ->
				$delegate.fcall = (fn) ->
					d = $delegate.defer()
					d.resolve(fn())
					return d.promise

				$delegate.isPromise = (val) ->
					return val.then?

				return $delegate
			])
		])

		Module.config(['$provide', ($provide) ->
			$provide.decorator('$state', ['$delegate', '$stateParams', ($delegate, $stateParams) ->
				###
				# Checks to see if a certain state is currently active
				#
				# @param {String} stateId The state to check. If it begins with a leading dot, we'll cehck
				#                         if the id exists anywhere in the current state. E.g., shorter to write '.create' than 'x.y.z.create'
				# @param {Object} stateParams If provided, then the params specified must also match
				###
				$delegate.isStateActive = (stateId, stateParams = null) ->
					if not $delegate.current then return false

					if stateParams
						if stateId.charAt(0) == '.'
							if $delegate.current.name.indexOf(stateId) == -1
								return false
						else
							if $delegate.current.name != stateId
								return false

						if not $delegate.$current.params then return false
						for own k, v of stateParams
							if not $stateParams[k]? or $stateParams[k] != v
								return false

						return true

					else
						if stateId.charAt(0) == '.'
							return $delegate.current.name.indexOf(stateId) != -1
						else
							return $delegate.current.name == stateId

				return $delegate
			])
		])