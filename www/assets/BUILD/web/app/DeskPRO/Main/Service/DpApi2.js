define(['DeskPRO/Main/Service/DpApi'], function(DpApi) {
  class DpApi2 extends DpApi {
    _getEndpointUrl(endpoint) {
      return `${this.api_url}/v2/${endpoint}`;
    }

    handleError(data, status, headers, config) {
      if (401 === status) {
        return window.location.reload(true);
      } else if (console) {
        return console.error(data);
      }
    }
  }
  return DpApi2;
});
