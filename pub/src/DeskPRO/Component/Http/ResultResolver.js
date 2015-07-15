export default class ResultResolver {
  /**
   * Called when the response comes back.
   *
   * @param {HttpResponse} http_response
   * @returns {*}
   */
  response(http_response) {
    return http_response;
  }

  /**
   * Called when there is a rejection when handling the response.
   *
   * @param {Object} rejection
   * @returns {*}
   */
  responseError(rejection) {
    return rejection;
  }
}
