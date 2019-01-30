define ['DeskPRO/Main/Service/DpApi'], (DpApi) ->
  class DpApi2 extends DpApi
    _getEndpointUrl: (endpoint) ->
      "#{@api_url}/v2/#{endpoint}"

    handleError: (data, status, headers, config) ->
      if 401 == status
        window.location.reload(true)
      else if console
        console.error data
