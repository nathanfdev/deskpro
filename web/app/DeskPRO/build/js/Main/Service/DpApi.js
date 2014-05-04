(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {
    var DpApi;
    return DpApi = (function() {
      function DpApi($http, api_url, api_token) {
        this.$http = $http;
        this.api_token = api_token;
        this.api_url = api_url.replace(/\/$/, '');
      }


      /**
      		* Format an endpoint with GET params to a full URL string.
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} Params to send in the query string
        	* @return {String}
       */

      DpApi.prototype.formatUrl = function(endpoint, params) {
        var itm, k, url, v, _i, _len;
        if (params == null) {
          params = null;
        }
        endpoint = endpoint.replace(/^\//, '');
        url = "" + this.api_url + "/" + endpoint;
        if (params) {
          if (url.indexOf('?') === -1) {
            url += '?';
          } else {
            url += '&';
          }
          if (Util.isArray(params)) {
            for (_i = 0, _len = params.length; _i < _len; _i++) {
              itm = params[_i];
              k = encodeURIComponent(itm.name);
              v = encodeURIComponent(itm.value);
              url += "" + k + "=" + v + "&";
            }
          } else {
            url += this._formatUrlObject(params);
          }
        }
        url = url.replace(/&$/, '');
        return url;
      };

      DpApi.prototype._formatUrlObject = function(obj, baseName) {
        var k, url, v, _results;
        if (baseName == null) {
          baseName = false;
        }
        url = '';
        _results = [];
        for (k in obj) {
          if (!__hasProp.call(obj, k)) continue;
          v = obj[k];
          if (v === null) {
            continue;
          }
          if (baseName) {
            k = baseName + '[' + encodeURIComponent(k) + ']';
          } else {
            k = encodeURIComponent(k);
          }
          if (Util.isObject(v)) {
            _results.push(url += this._formatUrlObject(v, k));
          } else {
            v = encodeURIComponent(v);
            _results.push(url += "" + k + "=" + v + "&");
          }
        }
        return _results;
      };


      /**
      		* Uses the api-caller endpoint to fetch multiple data points at once.
        	*
        	* @param {Array/Object} paths An array of paths, or a hash of paths. The returned data will be keyed by API endpoint
        	*                             name (if `paths` was an array), or by a string ID (the keys of `paths` if it was an object)
        	* @param {Object} http_params The HTTP params to send with the request
        	* @return {Promise}
       */

      DpApi.prototype.sendDataGet = function(paths, http_params) {
        var params, path, save_key, _i, _len;
        if (http_params == null) {
          http_params = {};
        }
        params = [];
        if (Util.isArray(paths)) {
          for (_i = 0, _len = paths.length; _i < _len; _i++) {
            path = paths[_i];
            if (path === null) {
              continue;
            }
            params.push({
              name: 'load_data[]',
              value: this.formatUrl(path)
            });
          }
        } else {
          for (save_key in paths) {
            if (!__hasProp.call(paths, save_key)) continue;
            path = paths[save_key];
            if (path === null) {
              continue;
            }
            params.push({
              name: 'load_data[' + encodeURIComponent(save_key) + ']',
              value: this.formatUrl(path)
            });
          }
        }
        return this.sendGet('api_caller', params, http_params);
      };


      /**
      		* Format an endpoint with GET params to a full URL string.
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} Params to send in the query string
        	* @return {String}
       */

      DpApi.prototype.prepareHttpParams = function(http_params) {
        var headers;
        if (http_params == null) {
          http_params = {};
        }
        headers = http_params.headers || {};
        headers["X-DeskPRO-API-Token"] = this.api_token;
        if (http_params.cache == null) {
          http_params.cache = false;
        }
        http_params.headers = headers;
        return http_params;
      };


      /*
        	* Sends a GET request
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} params to send in th query string
        	* @return {Promise}
       */

      DpApi.prototype.sendGet = function(endpoint, params, http_params) {
        var url;
        if (params == null) {
          params = null;
        }
        if (http_params == null) {
          http_params = {};
        }
        url = this.formatUrl(endpoint, params);
        http_params.method = 'GET';
        http_params.url = url;
        this.prepareHttpParams(http_params);
        return this.$http(http_params);
      };


      /*
        	* Sends a POST request with post_data as an encoded form.
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} post_data Params to send as the POST data (must be k:v object, or array of {name:k, value:v}
        	* @param {Object/Array} params to send in th query string
        	* @param {Object} http_params Params that will be written to
        	* @return {Promise}
       */

      DpApi.prototype.sendPost = function(endpoint, post_data, params, http_params) {
        var data_str, itm, k, url, v, _i, _len;
        if (post_data == null) {
          post_data = null;
        }
        if (params == null) {
          params = null;
        }
        if (http_params == null) {
          http_params = {};
        }
        url = this.formatUrl(endpoint, params);
        data_str = '';
        if (post_data) {
          if (Util.isArray(post_data)) {
            for (_i = 0, _len = post_data.length; _i < _len; _i++) {
              itm = post_data[_i];
              k = encodeURIComponent(itm.name);
              v = encodeURIComponent(itm.value);
              data_str += "" + k + "=" + v + "&";
            }
          } else {
            for (k in post_data) {
              if (!__hasProp.call(post_data, k)) continue;
              v = post_data[k];
              k = encodeURIComponent(k);
              v = encodeURIComponent(v);
              data_str += "" + k + "=" + v + "&";
            }
          }
          data_str = data_str.replace(/&$/, '');
        }
        http_params.method = 'POST';
        http_params.url = url;
        http_params.data = data_str;
        this.prepareHttpParams(http_params);
        http_params.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
        return this.$http(http_params);
      };


      /*
        	* Sends a POST request with a JSON payload
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} post_data Params to send as the POST data
        	* @param {Object/Array} params to send in th query string
        	* @param {Object} http_params Params that will be written to
        	* @return {Promise}
       */

      DpApi.prototype.sendPostJson = function(endpoint, post_data, params, http_params) {
        var url;
        if (post_data == null) {
          post_data = null;
        }
        if (params == null) {
          params = null;
        }
        if (http_params == null) {
          http_params = {};
        }
        url = this.formatUrl(endpoint, params);
        http_params.method = 'POST';
        http_params.url = url;
        http_params.data = post_data;
        this.prepareHttpParams(http_params);
        return this.$http(http_params);
      };


      /*
        	* Sends a PUT request with post_data as an encoded form
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} post_data Params to send as the POST data (must be k:v object, or array of {name:k, value:v}
        	* @param {Object/Array} params to send in th query string
        	* @param {Object} http_params Params that will be written to
        	* @return {Promise}
       */

      DpApi.prototype.sendPut = function(endpoint, post_data, params, http_params) {
        var data_str, itm, k, url, v, _i, _len;
        if (post_data == null) {
          post_data = null;
        }
        if (params == null) {
          params = null;
        }
        if (http_params == null) {
          http_params = {};
        }
        url = this.formatUrl(endpoint, params);
        data_str = '';
        if (post_data) {
          if (Util.isArray(params)) {
            for (_i = 0, _len = params.length; _i < _len; _i++) {
              itm = params[_i];
              k = encodeURIComponent(itm.name);
              v = encodeURIComponent(itm.value);
              data_str += "" + k + "=" + v + "&";
            }
          } else {
            for (k in params) {
              v = params[k];
              k = encodeURIComponent(k);
              v = encodeURIComponent(v);
              data_str += "" + k + "=" + v + "&";
            }
          }
          data_str = data_str.replace(/&$/, '');
        }
        http_params.method = 'PUT';
        http_params.url = url;
        if (post_data) {
          http_params.data = data_str;
        }
        this.prepareHttpParams(http_params);
        http_params.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
        return this.$http(http_params);
      };


      /*
        	* Sends a PUT request with a JSON payload
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} post_data Params to send as the POST data
        	* @param {Object/Array} params to send in th query string
        	* @param {Object} http_params Params that will be written to
        	* @return {Promise}
       */

      DpApi.prototype.sendPutJson = function(endpoint, post_data, params, http_params) {
        var url;
        if (post_data == null) {
          post_data = null;
        }
        if (params == null) {
          params = null;
        }
        if (http_params == null) {
          http_params = {};
        }
        url = this.formatUrl(endpoint, params);
        http_params.method = 'PUT';
        http_params.url = url;
        http_params.data = post_data;
        this.prepareHttpParams(http_params);
        return this.$http(http_params);
      };


      /*
        	* Sends a DELETE request
        	*
        	* @param {String} endpoint
        	* @param {Object/Array} params to send in th query string
        	* @return {Promise}
       */

      DpApi.prototype.sendDelete = function(endpoint, params, http_params) {
        var url;
        if (params == null) {
          params = null;
        }
        if (http_params == null) {
          http_params = {};
        }
        url = this.formatUrl(endpoint, params);
        http_params.method = 'DELETE';
        http_params.url = url;
        this.prepareHttpParams(http_params);
        return this.$http(http_params);
      };

      return DpApi;

    })();
  });

}).call(this);

//# sourceMappingURL=DpApi.js.map
