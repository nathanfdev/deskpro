define(['DeskPRO/Util/Util'], (Util) => {
  class DpApi {
    constructor($http, api_url, api_token, Growl) {
      this.handleError = this.handleError.bind(this);
      this.Growl = Growl;
      this.$http     = $http;
      this.api_token = api_token;
      this.api_url   = api_url.replace(/\/$/, '');
    }

    /*
    * Builds an endpoint URL.
    */
    buildEndpointAPIUrl(endpoint) { return `${window.location.protocol}//${window.location.host}${this._getEndpointUrl(endpoint)}`; }


    /*
    * Retrieve the full endpoint URL.
    */
    _getEndpointUrl(endpoint) {
      return `${this.api_url}/${endpoint}`;
    }

    /**
    * Format an endpoint with GET params to a full URL string.
      *
      * @param {String} endpoint
      * @param {Object/Array} Params to send in the query string
      * @return {String}
    */
    formatUrl(endpoint, params = null) {
      endpoint = endpoint.replace(/^\//, '');
      let url = this._getEndpointUrl(endpoint);

      if (params) {
        if (url.indexOf('?') === -1) {
          url += '?';
        } else {
          url += '&';
        }

        if (Util.isArray(params)) {
          for (const itm of Array.from(params)) {
            const k = encodeURIComponent(itm.name);
            const v = encodeURIComponent(itm.value);
            url += `${k}=${v}&`;
          }
        } else {
          url += this._formatUrlObject(params);
        }
      }

      url = url.replace(/&$/, '');

      return url;
    }

    _formatUrlObject(obj, baseName) {
      if (baseName == null) { baseName = false; }
      let url = '';
      for (let k of Object.keys(obj || {})) {
        let v = obj[k];
        if (v === null) { continue; }
        if (baseName) {
          k = `${baseName}[${encodeURIComponent(k)}]`;
        } else {
          k = encodeURIComponent(k);
        }

        if (Util.isObject(v)) {
          url += this._formatUrlObject(v, k);
        } else {
          v = encodeURIComponent(v);
          url += `${k}=${v}&`;
        }
      }
      return url;
    }

    /**
    * Uses the api-caller endpoint to fetch multiple data points at once.
      *
      * @param {Array/Object} paths An array of paths, or a hash of paths. The returned data will be keyed by API endpoint
      *                             name (if `paths` was an array), or by a string ID (the keys of `paths` if it was an object)
      * @param {Object} http_params The HTTP params to send with the request
      * @return {Promise}
    */
    sendDataGet(paths, http_params) {
      let path;
      if (http_params == null) { http_params = {}; }
      const params = [];
      if (Util.isArray(paths)) {
        for (path of Array.from(paths)) {
          if (path === null) { continue; }
          params.push({
            name:  'load_data[]',
            value: this.formatUrl(path)
          });
        }
      } else {
        for (const save_key of Object.keys(paths || {})) {
          path = paths[save_key];
          if (path === null) { continue; }
          params.push({
            name:  `load_data[${encodeURIComponent(save_key)}]`,
            value: this.formatUrl(path)
          });
        }
      }

      return this.sendGet('api_caller', params, http_params);
    }

    /**
    * Format an endpoint with GET params to a full URL string.
      *
      * @param {String} endpoint
      * @param {Object/Array} Params to send in the query string
      * @return {String}
    */
    prepareHttpParams(http_params) {
      if (http_params == null) { http_params = {}; }
      const headers = http_params.headers || {};
      headers['X-DeskPRO-API-Token'] = this.api_token;

      if ((http_params.cache == null)) {
        http_params.cache = false;
      }

      http_params.headers = headers;
      return http_params;
    }


    /*
      * Sends a GET request
      *
      * @param {String} endpoint
      * @param {Object/Array} params to send in th query string
      * @return {Promise}
    */
    sendGet(endpoint, params = null, http_params) {
      if (http_params == null) { http_params = {}; }
      const url = this.formatUrl(endpoint, params);

      http_params.method = 'GET';
      http_params.url    = url;
      this.prepareHttpParams(http_params);

      return this.sendRequest(http_params);
    }


    /*
      * Sends a POST request with post_data as an encoded form.
      *
      * @param {String} endpoint
      * @param {Object/Array} post_data Params to send as the POST data (must be k:v object, or array of {name:k, value:v}
      * @param {Object/Array} params to send in th query string
      * @param {Object} http_params Params that will be written to
      * @return {Promise}
    */
    sendPost(endpoint, post_data = null, params = null, http_params) {
      if (http_params == null) { http_params = {}; }
      const url = this.formatUrl(endpoint, params);

      let data_str = '';
      if (post_data) {
        let k,
          v;
        if (Util.isArray(post_data)) {
          for (const itm of Array.from(post_data)) {
            k = encodeURIComponent(itm.name);
            v = encodeURIComponent(itm.value);
            data_str += `${k}=${v}&`;
          }
        } else {
          for (k of Object.keys(post_data || {})) {
            v = post_data[k];
            k = encodeURIComponent(k);
            v = encodeURIComponent(v);
            data_str += `${k}=${v}&`;
          }
        }

        data_str = data_str.replace(/&$/, '');
      }

      http_params.method = 'POST';
      http_params.url    = url;
      http_params.data   = data_str;
      this.prepareHttpParams(http_params);

      http_params.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';

      return this.sendRequest(http_params);
    }


    /*
      * Sends a POST request with a JSON payload
      *
      * @param {String} endpoint
      * @param {Object/Array} post_data Params to send as the POST data
      * @param {Object/Array} params to send in th query string
      * @param {Object} http_params Params that will be written to
      * @return {Promise}
    */
    sendPostJson(endpoint, post_data = null, params = null, http_params) {
      if (http_params == null) { http_params = {}; }
      const url = this.formatUrl(endpoint, params);
      http_params.method = 'POST';
      http_params.url    = url;
      http_params.data   = post_data;
      this.prepareHttpParams(http_params);

      return this.sendRequest(http_params);
    }


    /*
      * Sends a PUT request with post_data as an encoded form
      *
      * @param {String} endpoint
      * @param {Object/Array} post_data Params to send as the POST data (must be k:v object, or array of {name:k, value:v}
      * @param {Object/Array} params to send in th query string
      * @param {Object} http_params Params that will be written to
      * @return {Promise}
    */
    sendPut(endpoint, post_data = null, params = null, http_params) {
      if (http_params == null) { http_params = {}; }
      const url = this.formatUrl(endpoint, params);

      let data_str = '';
      if (post_data) {
        let k,
          v;
        if (Util.isArray(params)) {
          for (const itm of Array.from(params)) {
            k = encodeURIComponent(itm.name);
            v = encodeURIComponent(itm.value);
            data_str += `${k}=${v}&`;
          }
        } else {
          for (k in params) {
            v = params[k];
            k = encodeURIComponent(k);
            v = encodeURIComponent(v);
            data_str += `${k}=${v}&`;
          }
        }

        data_str = data_str.replace(/&$/, '');
      }

      http_params.method = 'PUT';
      http_params.url    = url;
      if (post_data) { http_params.data   = data_str; }
      this.prepareHttpParams(http_params);

      http_params.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';

      return this.sendRequest(http_params);
    }


    /*
      * Sends a PUT request with a JSON payload
      *
      * @param {String} endpoint
      * @param {Object/Array} post_data Params to send as the POST data
      * @param {Object/Array} params to send in th query string
      * @param {Object} http_params Params that will be written to
      * @return {Promise}
    */
    sendPutJson(endpoint, post_data = null, params = null, http_params) {
      if (http_params == null) { http_params = {}; }
      const url = this.formatUrl(endpoint, params);
      http_params.method = 'PUT';
      http_params.url    = url;
      http_params.data   = post_data;
      this.prepareHttpParams(http_params);

      return this.sendRequest(http_params);
    }


    /*
      * Sends a DELETE request
      *
      * @param {String} endpoint
      * @param {Object/Array} params to send in th query string
      * @return {Promise}
    */
    sendDelete(endpoint, params = null, http_params) {
      if (http_params == null) { http_params = {}; }
      const url = this.formatUrl(endpoint, params);

      http_params.method = 'DELETE';
      http_params.url    = url;
      this.prepareHttpParams(http_params);

      return this.sendRequest(http_params);
    }


    sendRequest(http_params) {
      const result = this.$http(http_params);
      result.error(this.handleError);

      return result;
    }

    handleError(data, status, headers, config) {
      if ((status === 500) && this.Growl) {
        return this.Growl.error('There was a problem processing your last request. Please try again.');
      } else if ((status === 403) && (data.error_code !== 'insufficient_rights')) {
        return window.location.reload(true);
      } else if (console) {
        return console.info(data);
      }
    }
  }

  return DpApi;
});
