export class HttpResponse {

  /**
   * @param {jqXHR}  xhr
   * @param {String} status
   * @param {Object} config
   * @param {*}      data
   */
  constructor(xhr, status, config, data = null) {
    this.xhr = xhr;
    this.status = status;
    this.config = config;
    this.data = data;
  }

  /**
   * @returns {jqXHR}
   */
  getXhr() {
    return this.xhr;
  }

  /**
   * Returns the status of the request:
   * - complete
   * - error
   * - timeout
   * - abort
   * - parseerror
   *
   * @returns {String}
   */
  getStatus() {
    return this.status;
  }

  /**
   * Gets the HTTP response code (e.g., 200, 301, 404, etc).
   *
   * @returns {Integer}
   */
  getResponseCode() {
    return this.xhr.status;
  }

  /**
   * Gets the response text.
   *
   * @returns {String}
   */
  getRawData() {
    return this.xhr.responseText;
  }

  /**
   * Gets the decoded response (e.g., JSON data returns an object).
   *
   * @returns {*}
   */
  getData() {
    return this.data;
  }

  /**
   * Get a header that came back with the response.
   *
   * @param {String} n
   * @returns {String}
   */
  getResponseHeader(n) {
    return this.xhr.getResponseHeader(n);
  }

  /**
   * Get the original config object used to send the request.
   *
   * @returns {Object}
   */
  getConfig() {
    return this.config;
  }

  /**
   * Check if this request is an error. Either an error status or an error code response from the server.
   *
   * @returns {Boolean}
   */
  isError() {
    return this.isErrorStatus() || this.isErrorResponse();
  }

  /**
   * Check if this request state is an error status.
   *
   * @returns {Boolean}
   */
  isErrorStatus() {
    return this.status === 'error' || this.status === 'timeout' || this.status === 'abort' || this.status === 'parsererror';
  }

  /**
   * Check if the return status from the server is an error status.
   *
   * @returns {Boolean}
   */
  isErrorResponse() {
    let c = this.getResponseCode();
    return !c || !(c >= 200 && c < 300);
  }
}
