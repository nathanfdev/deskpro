define ['angular'], (angular) ->
	class DpApi
		constructor: ($http, api_url, api_token) ->
			@$http     = $http
			@api_token = api_token
			@api_url   = api_url.replace(/\/$/, '')

		###*
		* Format an endpoint with GET params to a full URL string.
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} Params to send in the query string
    	* @return {String}
		###
		formatUrl: (endpoint, params = null) ->
			endpoint = endpoint.replace(/^\//, '')
			url = "#{@api_url}/#{endpoint}"

			if params
				if url.indexOf('?') == -1
					url += '?'
				else
					url += '&'

				if angular.isArray(params)
					for itm in params
						k = encodeURIComponent(itm.name)
						v = encodeURIComponent(itm.value)
						url += "#{k}=#{v}&"
				else
					for k, v of params
						k = encodeURIComponent(k)
						v = encodeURIComponent(v)
						url += "#{k}=#{v}&"

				url = url.replace(/&$/, '')

			return url


		###*
		* Uses the api-caller endpoint to fetch multiple data points at once.
    	*
    	* @return {Promise}
		###
		sendDataGet: (paths, http_params = {}) ->
			params = []
			for path in paths
				params.push({
					name: 'load_data[]',
					value: @formatUrl(path)
				})

			return @sendGet('api_caller', params, http_params)

		###*
		* Format an endpoint with GET params to a full URL string.
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} Params to send in the query string
    	* @return {String}
		###
		prepareHttpParams: (http_params = {}) ->
			headers = http_params.headers || {}
			headers["X-DeskPRO-API-Token"] = @api_token

			if not http_params.cache?
				http_params.cache = false

			http_params.headers = headers
			return http_params


		###
    	* Sends a GET request
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} params to send in th query string
    	* @return {Promise}
		###
		sendGet: (endpoint, params = null, http_params = {}) ->
			url = @formatUrl(endpoint, params)

			http_params.method = 'GET'
			http_params.url    = url
			@prepareHttpParams(http_params)

			return @$http(http_params)


		###
    	* Sends a POST request with post_data as an encoded form.
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} post_data Params to send as the POST data (must be k:v object, or array of {name:k, value:v}
    	* @param {Object/Array} params to send in th query string
    	* @param {Object} http_params Params that will be written to
    	* @return {Promise}
		###
		sendPost: (endpoint, post_data = null, params = null, http_params = {}) ->
			url = @formatUrl(endpoint, params)

			data_str = ''
			if post_data
				if angular.isArray(post_data)
					for itm in post_data
						k = encodeURIComponent(itm.name)
						v = encodeURIComponent(itm.value)
						data_str += "#{k}=#{v}&"
				else
					for own k, v of post_data
						k = encodeURIComponent(k)
						v = encodeURIComponent(v)
						data_str += "#{k}=#{v}&"

				data_str = data_str.replace(/&$/, '')

			http_params.method = 'POST'
			http_params.url    = url
			http_params.data   = data_str
			@prepareHttpParams(http_params)

			return @$http(http_params)


		###
    	* Sends a POST request with a JSON payload
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} post_data Params to send as the POST data
    	* @param {Object/Array} params to send in th query string
    	* @param {Object} http_params Params that will be written to
    	* @return {Promise}
		###
		sendPostJson: (endpoint, post_data = null, params = null, http_params = {}) ->
			url = @formatUrl(endpoint, params)
			http_params.method = 'POST'
			http_params.url    = url
			http_params.data   = post_data
			@prepareHttpParams(http_params)

			return @$http(http_params)


		###
    	* Sends a PUT request with post_data as an encoded form
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} post_data Params to send as the POST data (must be k:v object, or array of {name:k, value:v}
    	* @param {Object/Array} params to send in th query string
    	* @param {Object} http_params Params that will be written to
    	* @return {Promise}
		###
		sendPut: (endpoint, post_data = null, params = null, http_params = {}) ->
			url = @formatUrl(endpoint, params)

			data_str = ''
			if post_data
				if angular.isArray(params)
					for itm in params
						k = encodeURIComponent(itm.name)
						v = encodeURIComponent(itm.value)
						data_str += "#{k}=#{v}&"
				else
					for k, v of params
						k = encodeURIComponent(k)
						v = encodeURIComponent(v)
						data_str += "#{k}=#{v}&"

				data_str = data_str.replace(/&$/, '')

			http_params.method = 'PUT'
			http_params.url    = url
			http_params.data   = data_str if post_data
			@prepareHttpParams(http_params)

			return @$http(http_params)


		###
    	* Sends a PUT request with a JSON payload
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} post_data Params to send as the POST data
    	* @param {Object/Array} params to send in th query string
    	* @param {Object} http_params Params that will be written to
    	* @return {Promise}
		###
		sendPutJson: (endpoint, post_data = null, params = null, http_params = {}) ->
			url = @formatUrl(endpoint, params)
			http_params.method = 'PUT'
			http_params.url    = url
			http_params.data   = post_data
			@prepareHttpParams(http_params)

			return @$http(http_params)


		###
    	* Sends a DELETE request
    	*
    	* @param {String} endpoint
    	* @param {Object/Array} params to send in th query string
    	* @return {Promise}
		###
		sendDelete: (endpoint, params = null, http_params = {}) ->
			url = @formatUrl(endpoint, params)

			http_params.method = 'DELETE'
			http_params.url    = url
			@prepareHttpParams(http_params)

			return @$http(http_params)
