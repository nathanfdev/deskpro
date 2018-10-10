define ['DeskPRO/Util/Util'], (Util) ->
  class LangSyncApi
    constructor: ($http, api_url, @Growl) ->
      @$http     = $http
      @api_url   = api_url.replace(/\/$/, '')

    ###
    * Retrieve the full endpoint URL.
    ###
    _getEndpointUrl: (endpoint) ->
        "#{@api_url}/{endpoint}"

    ###*
    * Format an endpoint with GET params to a full URL string.
      *
      * @param {String} endpoint
      * @param {Object/Array} Params to send in the query string
      * @return {String}
    ###
    formatUrl: (endpoint, params = null) ->
      endpoint = endpoint.replace(/^\//, '')
      url = @_getEndpointUrl(endpoint)

      if params
        if url.indexOf('?') == -1
          url += '?'
        else
          url += '&'

        if Util.isArray(params)
          for itm in params
            k = encodeURIComponent(itm.name)
            v = encodeURIComponent(itm.value)
            url += "#{k}=#{v}&"
        else
          url += @_formatUrlObject(params)

      url = url.replace(/&$/, '')

      return url

    _formatUrlObject: (obj, baseName = false) ->
      url = ''
      for own k, v of obj
        if v == null then continue
        if baseName
          k = baseName + '[' + encodeURIComponent(k) + ']'
        else
          k = encodeURIComponent(k)

        if Util.isObject(v)
          url += @_formatUrlObject(v, k)
        else
          v = encodeURIComponent(v)
          url += "#{k}=#{v}&"
      url

    ###*
    * Format an endpoint with GET params to a full URL string.
      *
      * @param {String} endpoint
      * @param {Object/Array} Params to send in the query string
      * @return {String}
    ###
    prepareHttpParams: (http_params = {}) ->
      headers = http_params.headers || {}

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

      @sendRequest http_params


    sendRequest: (http_params) ->
      result = @$http http_params
      result.error @handleError

      result

    handleError: (data, status, headers, config) =>
      if 500 == status && @Growl
        @Growl.error 'There was a problem processing your last request. Please try again.'
      else if console
        console.info data
