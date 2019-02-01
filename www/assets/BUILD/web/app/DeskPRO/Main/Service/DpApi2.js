// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
