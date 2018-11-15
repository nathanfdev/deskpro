define ['DeskPRO/Util/Util'], (Util) ->
  class LangSyncApi
    constructor: ($http, api_url, @Growl) ->
      @$http     = $http
      @api_url   = api_url.replace(/\/$/, '')

    ###
    * Retrieve the full endpoint URL.
    ###
    _getEndpointUrl: (endpoint) ->
        "#{@api_url}/#{endpoint}"

    formatUrl: (endpoint) ->
      endpoint = endpoint.replace(/^\//, '')
      url = @_getEndpointUrl(endpoint)
      url = url.replace(/&$/, '')

      return url

    getManifest: () ->
      url = @formatUrl('/locales/manifest.json');

      http_params = {
        method: 'GET',
        url: url
      }

      @sendRequest http_params

    getPhrases: (locale, type) ->
      url = @formatUrl("/locales/#{locale}/#{type}.json");

      http_params = {
        method: 'GET',
        url: url
      }

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
