define ['DeskPRO/Util/Util'], (Util) ->
	return (Module) ->
		Module.factory('dpHttpInterceptor', ['$q', ($q) ->
			updateTimes = []

			# This var is used in browser tests so we can
			# properly wait for a page to be finished loading
			window.DP_AJAX_RUNNINGCOUNT = 0
			addRunningCount = ->
				window.DP_AJAX_RUNNINGCOUNT++
			subRunningCount = ->
				window.DP_AJAX_RUNNINGCOUNT--
				if window.DP_AJAX_RUNNINGCOUNT < 0
					window.DP_AJAX_RUNNINGCOUNT = 0

			return {
				request: (config) ->
					addRunningCount()

					if window.DP_SESSION_ID and not config.headers?['X-DeskPRO-Session-ID']?
						config.headers['X-DeskPRO-Session-ID'] = window.DP_SESSION_ID
					if window.DP_REQUEST_TOKEN and not config.headers?['X-DeskPRO-Request-Token']?
						config.headers['X-DeskPRO-Request-Token'] = window.DP_REQUEST_TOKEN

					if config.headers?['X-DeskPRO-API-Token']?
						config.startTime = new Date()

						next = updateTimes.pop()
						if next
							if config.url.indexOf('?') == -1
								config.url += '?'
							else
								config.url += '&'

							#timeEnc = ((next.timeTaken / 1000) + "").replace(/\./, '_')
							#config.url += "__dp_reqtime=#{next.requestId}_t#{timeEnc}"

					config.url = config.url.replace(/^DP_URL\//g, window.DP_BASE_URL.replace(/\/+$/, '')+'/')

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

		Module.config(['$provide', ($provide) ->
			$provide.decorator('$http', ($delegate) ->
				$delegate.formatApiUrl = (endpoint, params, signed = true) ->
					endpoint = endpoint.replace(/^\//, '')
					url = "#{window.DP_BASE_API_URL}/#{endpoint}"

					if params
						url += if url.indexOf('?') == -1 then '?' else '&'
						if Util.isArray(params)
							for itm in params
								k = encodeURIComponent(itm.name)
								v = encodeURIComponent(itm.value)
								url += "#{k}=#{v}&"
						else
							url += @_formatUrlObject(params)

					url = url.replace(/&$/, '')

					if signed then url = this.signUrl(url)

					return url

				$delegate.signUrl = (url) ->
					url += if url.indexOf('?') == -1 then '?' else '&'
					url += 'API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN
					return url

				return $delegate
			)
		])