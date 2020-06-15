define(['DeskPRO/Util/Util'], function(Util) {
  class LangSyncApi {
    constructor($http, api_url, Growl, Api2) {
      this.handleError = this.handleError.bind(this);
      this.Growl   = Growl;
      this.Api2    = Api2;
      this.$http   = $http;
      this.api_url = api_url.replace(/\/$/, '');
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

    getDeskproManifest() {
      const url = this.formatUrl('/locales/manifest.json');
      const httpParams = {
        method:        'GET',
        url,
        isCorsRequest: true
      };

      return this.sendRequest(httpParams);
    }

    getCrowdinLocales() {
      return this.Api2.sendGet('/languages/crowdin/locales');
    }

    getPhrases(locale, type) {
      if (type === 'helpcenter') {
        return this.Api2.sendGet(`/languages/crowdin/${locale}/${type}/phrases`);
      }

      const url = this.formatUrl(`/locales/${locale}/${type}.json`);
      const httpParams = {
        method:        'GET',
        url,
        isCorsRequest: true
      };

      return this.sendRequest(httpParams);
    }

    sendRequest(httpParams) {
      const result = this.$http(httpParams);
      result.error(this.handleError);

      return result;
    }

    handleError(data, status, headers, config) {
      if ((status === 500) && this.Growl) {
        return this.Growl.error('There was a problem processing your last request. Please try again.');
      } else if (console) {
        return console.info(data);
      }
    }
  }

  return LangSyncApi;
});
