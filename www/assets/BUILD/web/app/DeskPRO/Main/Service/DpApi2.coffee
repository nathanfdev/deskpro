define ['DeskPRO/Main/Service/DpApi'], (DpApi) ->
    class DpApi2 extends DpApi
        _getEndpointUrl: (endpoint) ->
            "#{@api_url}/v2/#{endpoint}"

        ###
        # We have to add X-Agent-Request custom header to have propper errors rendering in JSON.
        ###
        prepareHttpParams: (http_params = {}) ->
            params = super(http_params);
            headers = params.headers || {}
            headers["X-Agent-Request"] = true
            http_params.headers = headers
            return http_params