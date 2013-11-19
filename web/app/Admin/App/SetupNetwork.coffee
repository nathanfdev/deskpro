define ->
	return (Module) ->
		Module.factory('dpHttpInterceptor', [ ->
			updateTimes = []

			return {
				request: (config) ->
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
					return rejection

				responseError: (rejection) ->
					return rejection
			}
		])

		Module.config(['$httpProvider', 'fileUploadProvider', ($httpProvider, fileUploadProvider) ->
			$httpProvider.interceptors.push('dpHttpInterceptor');

			angular.extend(fileUploadProvider.defaults, {
				headers: {'X-DeskPRO-API-Token': window.DP_API_TOKEN}
			});
		])