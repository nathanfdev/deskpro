define ['DeskPRO/Main/Service/DpApi'], (DpApi) ->
    class DpApi2 extends DpApi
        _getEndpointUrl: (endpoint) ->
            "#{@api_url}/v2/#{endpoint}"
