define ['DeskPRO/Util/Util'], (Util) ->
	return (Module) ->
		Module.factory('dpHttpInterceptor', ['$q', ($q) ->
			updateTimes = []

			# This var is used in browser tests so we can
			# properly wait for a page to be finished loading
			window.DP_AJAX_RUNNINGCOUNT = 0
			subTimeout = null
			subCounter = 0
			addRunningCount = ->
				window.DP_AJAX_RUNNINGCOUNT++
			subRunningCount = ->
				subCounter++
				if not subTimeout
					subTimeout = setTimeout(->
						subTimeout = null
						window.DP_AJAX_RUNNINGCOUNT -= subCounter
						subCounter = 0
					, 250)

			return {
				request: (config) ->
					addRunningCount()
					if config.headers?['X-DeskPRO-API-Token']?
						config.startTime = new Date()

						next = updateTimes.pop()
						if next
							if config.url.indexOf('?') == -1
								config.url += '?'
							else
								config.url += '&'

							timeEnc = ((next.timeTaken / 1000) + "").replace(/\./, '_')
							config.url += "__dp_reqtime=#{next.requestId}_t#{timeEnc}"

					return config

				response: (response) ->
					subRunningCount()
					if response.config.startTime
						headers = response.headers()
						if headers['x-deskpro-requestid']?
							lastRequestId = headers['x-deskpro-requestid']
							lastRequestTime = ((new Date()).getTime()) - response.config.startTime.getTime()

							updateTimes.push({
								timeTaken: lastRequestTime,
								requestId: lastRequestId
							})
					return response

				requestError: (rejection) ->
					subRunningCount()
					return $q.reject(rejection)

				responseError: (rejection) ->
					subRunningCount()
					return $q.reject(rejection)
			}
		])

		Module.config(['$httpProvider', 'fileUploadProvider', ($httpProvider, fileUploadProvider) ->
			$httpProvider.interceptors.push('dpHttpInterceptor');

			angular.extend(fileUploadProvider.defaults, {
				headers: {'X-DeskPRO-API-Token': window.DP_API_TOKEN}
			});
		])