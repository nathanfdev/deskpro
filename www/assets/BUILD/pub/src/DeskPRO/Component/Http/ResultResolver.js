export class ResultResolver {

  /**
   * Called when the response comes back.
   *
   * @param {HttpResponse} httpResponse
   * @returns {*}
   */
  response(httpResponse) {
    return httpResponse;
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
