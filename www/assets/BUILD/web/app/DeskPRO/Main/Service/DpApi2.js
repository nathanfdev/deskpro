define(['DeskPRO/Main/Service/DpApi'], (DpApi) => {
  class DpApi2 extends DpApi {
    _getEndpointUrl(endpoint) {
      return `${this.api_url}/v2/${endpoint}`;
    }

    handleError(data, status, headers, config) {
      if (status === 401) {
        return window.location.reload(true);
      } else if (console) {
        return console.error(data);
      }
    }
  }
  return DpApi2;
});
