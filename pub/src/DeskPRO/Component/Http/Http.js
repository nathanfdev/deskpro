import _ from "lodash";
import HttpResponse from "./HttpResponse";

export default class Http {
  constructor(ajaxFn) {
    this.ajaxFn = ajaxFn;

    this.defaults = {
      "ALL":    {},
      "GET":    {},
      "POST":   {},
      "PATCH":  {},
      "PUT":    {},
      "DELETE": {}
    };

    this.interceptors = [];
    this.resultResolvers = [];
    this.init();
  }

  /**
   * Called during construc. Meant as a hook point for sub-classes.
   */
  init() {
    // add stuff
  }

  /**
   * Set a default header on all request.
   *
   * @param {String} headerName
   * @param {String} headerValue
   * @param {String} type
   */
  setDefaultHeader(headerName, headerValue, type = "ALL") {
    type = type.toUpperCase();
    if (!this.defaults[type].headers) {
      this.defaults[type].headers = {};
    }
    this.defaults[type].headers[headerName] = headerValue;
  }

  /**
   * Set default config
   *
   * @param {String} configName
   * @param {*}      configValue
   * @param {String} type
   */
  setDefaultConfig(configName, configValue, type = "ALL") {
    type = type.toUpperCase();
    this.defaults[type][configName] = configValue;
  }

  /**
   * An interceptor is any HttpInterceptor object. Note that you can just pass
   * any object, it doesn't need to be an actual instance of HttpInterceptor (that exists just to define the type).
   *
   * @param {HttpInterceptor} interceptor
   */
  addInterceptor(interceptor) {
    this.interceptors.push(interceptor)
  }

  /**
   * A result resolver is run after interceptors. This allows you to re-define the actual result passed
   * back from making a result. E.g., usually this would be an HttpResponse, but maybe you want to change this.
   *
   * Note that these are basically the same as interceptors. The difference is that the return result of a ResultResolver
   * is not expected to be an HttpResponse.
   *
   * @param {ResultResolver} resultResolver
   */
  addResultResolver(resultResolver) {
    this.resultResolvers.push(resultResolver);
  }

  /**
   * For request types that submit data (POST, PUT, PATCH), submit a JSON-encoded
   * payload instead of encoding it as a form.
   *
   * @param {Boolean} on Turn it on or off
   */
  enableJsonPayloads(on = true) {
    if (on) {
      this.setDefaultConfig('jsonPayload', true);
    } else {
      this.setDefaultConfig('jsonPayload', false);
    }
  }

  /**
   * Applies defaults to config
   *
   * @param {Object} config
   * @returns {Object}
   * @private
   */
  _applyDefaultConfig(config) {

    if (!config.method) {
      config.method = 'GET';
    }

    config.method = config.method.toUpperCase();
    config.rawData = config.data || null;

    ["ALL", config.method].forEach(t => {
      _.forEach(this.defaults[t], (configValue, configName) => {
        if (configName == 'headers') {
          configValue.forEach((headerValue, headerName) => {
            if (!config.headers) {
              config.headers = {};
            }
            if (_.isUndefined(config.headers[headerName])) {
              config.headers[headerName] = headerValue;
            }
          });
        } else {
          if (_.isUndefined(config[configName])) {
            config[configName] = configValue;
          }
        }
      });
    });

    return config;
  }

  /**
   * Send a request.
   *
   * @param {Object} config
   * @returns {Promise}
   */
  send(config) {
    let sendReq = (config) => {
      config = this._applyDefaultConfig(config);

      if (config.method == 'POST' || config.method == 'PUT' || config.method == 'PATCH') {
        if (config.jsonPayload) {
          config.contentType = 'application/json';
          config.processData = false;
          config.data = JSON.stringify(config.data || {});
        }
      }

      if (config.transformRequest) {
        config = config.transformRequest(config);
      }

      return new Promise((ajaxResolve, ajaxReject) => {
        this.ajaxFn(config).done((data, textStatus, jqXHR) => {
          let r = new HttpResponse(jqXHR, textStatus, config, data);
          if (config.transformRequest) {
            r = config.transformRequest(r);
          }

          ajaxResolve(r);
        }).fail((jqXHR, textStatus, errorThrown) => {
          let r = new HttpResponse(jqXHR, textStatus, config, null);
          if (config.transformRequest) {
            r = config.transformRequest(r);
          }

          ajaxReject(r);
        });
      });
    };

    let chain = [sendReq, null];
    let promise = new Promise((resolve) => resolve(config));

    this.interceptors.forEach(i => {
      if (i.request || i.requestError) {
        chain.unshift(this._getBoundInterceptor(i.request, i), this._getBoundInterceptor(i.requestError, i));
      }
      if (i.response || i.responseError) {
        chain.push(this._getBoundInterceptor(i.response, i), this._getBoundInterceptor(i.responseError, i));
      }
    });

    this.resultResolvers.forEach(i => {
      if (i.response || i.responseError) {
        chain.push(this._getBoundInterceptor(i.response, i), this._getBoundInterceptor(i.responseError, i));
      }
    });

    while (chain.length) {
      let res = chain.shift();
      let err = chain.shift();
      promise = promise.then(
        () => this._callInterceptor(res, Array.prototype.slice.call(arguments)),
        () => this._callInterceptor(err, Array.prototype.slice.call(arguments))
      );
    }

    return promise;
  }

  _callInterceptor(i, v) {
    if (!_.isArray(v)) {
      v = [v];
    }
    return i.apply(v);
  }

  _getBoundInterceptor(i, s) {
    if (!i) {
      return null;
    } else if (_.isPlainObject(s)) {
      return i;
    } else {
      return _.bind(i, s);
    }
  }

  sendGet(url, config = {}) {
    config.url = url;
    config.method = 'GET';
    return this.send(config);
  }

  sendDelete(url, config = {}) {
    config.url = url;
    config.method = 'DELETE';
    return this.send(config);
  }

  sendHead(url, config = {}) {
    config.url = url;
    config.method = 'HEAD';
    return this.send(config);
  }

  sendJsonp(url, config = {}) {
    config.url = url;
    config.method = 'GET';
    config.dataType = 'jsonp';
    return this.send(config);
  }

  sendPost(url, data, config = {}) {
    config.url = url;
    config.data = data;
    config.method = 'POST';
    return this.send(config);
  }

  sendPut(url, data, config = {}) {
    config.url = url;
    config.data = data;
    config.method = 'PUT';
    return this.send(config);
  }

  sendPatch(url, data, config = {}) {
    config.url = url;
    config.data = data;
    config.method = 'PATCH';
    return this.send(config);
  }
}
