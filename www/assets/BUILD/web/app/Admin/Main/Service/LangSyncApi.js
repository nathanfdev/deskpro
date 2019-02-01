// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Util'], function(Util) {
  class LangSyncApi {
    constructor($http, api_url, Growl) {
      this.handleError = this.handleError.bind(this);
      this.Growl = Growl;
      this.$http     = $http;
      this.api_url   = api_url.replace(/\/$/, '');
    }

    /*
    * Retrieve the full endpoint URL.
    */
    _getEndpointUrl(endpoint) {
        return `${this.api_url}/${endpoint}`;
      }

    formatUrl(endpoint) {
      endpoint = endpoint.replace(/^\//, '');
      let url = this._getEndpointUrl(endpoint);
      url = url.replace(/&$/, '');

      return url;
    }

    getManifest() {
      const url = this.formatUrl('/locales/manifest.json');

      const http_params = {
        method: 'GET',
        url,
        isCorsRequest: true
      };

      return this.sendRequest(http_params);
    }

    getPhrases(locale, type) {
      const url = this.formatUrl(`/locales/${locale}/${type}.json`);

      const http_params = {
        method: 'GET',
        url,
        isCorsRequest: true
      };

      return this.sendRequest(http_params);
    }

    sendRequest(http_params) {
      const result = this.$http(http_params);
      result.error(this.handleError);

      return result;
    }

    handleError(data, status, headers, config) {
      if ((500 === status) && this.Growl) {
        return this.Growl.error('There was a problem processing your last request. Please try again.');
      } else if (console) {
        return console.info(data);
      }
    }
  }

  return LangSyncApi;
});
