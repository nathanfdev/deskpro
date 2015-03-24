export default class HttpInterceptor {
  /**
   * Called before the AJAX request is sent. Can return a config object
   * or a promise that resolves to a config object.
   *
   * @param {Object} config
   * @returns {Object/Promise}
   */
  request(config) {
    return config;
  }

  /**
   * Called when a request is rejected. That rejection can be anything, but most commonly is
   * a HttpResponse unless a previous interceptor has rejected it with something else.
   *
   * Should return an HttpResponse or a new promise.
   *
   * @param {Object} rejection
   * @returns {HttpResponse/Promise}
   */
  requestError(rejection) {
    return rejection;
  }

  /**
   * Called when the response comes back. The return value should be an HttpResponse or a promise.
   *
   * @param {HttpResponse} http_response
   * @returns {HttpResponse/Promise}
   */
  response(http_response) {
    return http_response;
  }

  /**
   * Called when there is a rejection when handling the response.
   *
   * Should return an HttpResponse or a Promise.
   *
   * @param {Object} rejection
   * @returns {HttpResponse/Promise}
   */
  responseError(rejection) {
    return rejection;
  }
}