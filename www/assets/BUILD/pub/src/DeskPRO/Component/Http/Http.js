import bind from 'lodash/bind';
import forEach from 'lodash/forEach';
import isPlainObject from 'lodash/isPlainObject';
import isUndefined from 'lodash/isUndefined';
import { HttpResponse } from './HttpResponse';

export class Http {

  constructor(ajaxFn) {
    this.ajaxFn = ajaxFn;

    this.defaults = {
      ALL:    {},
      GET:    {},
      POST:   {},
      PATCH:  {},
      PUT:    {},
      DELETE: {}
    };

    this.interceptors = [];
    this.resultResolvers = [];
    this.init();
  }

  /*
   * Called during constructor. Meant as a hook point for sub-classes.
   */
  init() { } // eslint-disable-line class-methods-use-this

  /*
   * Set a default header on all request.
   *
   * @param {String} headerName
   * @param {String} headerValue
   * @param {String} type
   */
  setDefaultHeader(headerName, headerValue, type = 'ALL') {
    const upperCaseType = type.toUpperCase();
    if (!this.defaults[upperCaseType].headers) {
      this.defaults[upperCaseType].headers = {};
    }
    this.defaults[upperCaseType].headers[headerName] = headerValue;
  }

  /*
   * Set default config
   *
   * @param {String} configName
   * @param {*}      configValue
   * @param {String} type
   */
  setDefaultConfig(configName, configValue, type = 'ALL') {
    this.defaults[type.toUpperCase()][configName] = configValue;
  }

  /*
   * An interceptor is any HttpInterceptor object. Note that you can just pass
   * any object, it doesn't need to be an actual instance of HttpInterceptor (that exists just to define the type).
   *
   * @param {HttpInterceptor} interceptor
   */
  addInterceptor(interceptor) {
    this.interceptors.push(interceptor);
  }

  /*
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

  /*
   * For request types that submit data (POST, PUT, PATCH), submit a JSON-encoded
   * payload instead of encoding it as a form.
   *
   * @param {Boolean} on Turn it on or off
   */
  enableJsonPayloads(on = true) {
    this.setDefaultConfig('jsonPayload', !!on);
  }

  /*
   * Applies defaults to config
   *
   * @param {Object} config
   * @returns {Object}
   * @private
   */
  applyDefaultConfig(config) {
    if (!config.method) {
      config.method = 'GET';
    }

    config.method = config.method.toUpperCase();
    config.rawData = config.data || null;

    ['ALL', config.method].forEach((t) => {
      forEach(this.defaults[t], (configValue, configName) => {
        if (configName === 'headers') {
          Object.keys(configValue).forEach((headerName) => {
            const headerValue = configValue[headerName];
            if (!config.headers) {
              config.headers = {};
            }
            if (isUndefined(config.headers[headerName])) {
              config.headers[headerName] = headerValue;
            }
          });
        } else if (isUndefined(config[configName])) {
          config[configName] = configValue;
        }
      });
    });

    return config;
  }

  /*
   * Send a request.
   *
   * @param {Object} requestConfig
   * @returns {Promise}
   */
  send(requestConfig) {
    const sendReq = (initialConfig) => {
      let config = this.applyDefaultConfig(initialConfig);

      if (config.method === 'POST' || config.method === 'PUT' || config.method === 'PATCH') {
        if (config.jsonPayload) {
          config.contentType = 'application/json';
          config.processData = false;
          config.data = JSON.stringify(config.data || {});
        }
      }
      if (config.crossDomain && config.dataType === 'json') {
        delete config.headers;
      }

      if (config.transformRequest) {
        config = config.transformRequest(config);
      }

      return new Promise((ajaxResolve, ajaxReject) => {
        this.ajaxFn(config).done((data, textStatus, jqXHR) => {
          let response = new HttpResponse(jqXHR, textStatus, config, data);
          if (config.transformResponse) {
            response = config.transformResponse(response);
          }

          ajaxResolve(response);
        }).fail((jqXHR, textStatus) => {
          const expectedJsonPayload = config.dataType === 'json' || ['application/json', 'text/javascript'].indexOf(jqXHR.getResponseHeader('Content-Type')) !== -1;
          const isEmptyString = jqXHR.responseText === '' || `${jqXHR.getResponseHeader('Content-Length')}` === '0';

          const isParseEmptyJsonStringError = textStatus === 'parsererror'
            && expectedJsonPayload
            && isEmptyString
          ;

          let response = new HttpResponse(
            jqXHR,
            isParseEmptyJsonStringError ? 'success' : textStatus,
            config,
            isParseEmptyJsonStringError ? null : jqXHR.responseJSON);

          if (config.transformResponse) {
            response = config.transformResponse(response);
          }

          if (isParseEmptyJsonStringError) {
            ajaxResolve(response);
          } else {
            ajaxReject(response);
          }
        });
      });
    };

    const chain = [sendReq, null];
    let promise = new Promise(resolve => resolve(requestConfig));

    this.interceptors.forEach((i) => {
      if (i.request || i.requestError) {
        chain.unshift(this.getBoundInterceptor(i.request, i), this.getBoundInterceptor(i.requestError, i));
      }
      if (i.response || i.responseError) {
        chain.push(this.getBoundInterceptor(i.response, i), this.getBoundInterceptor(i.responseError, i));
      }
    });

    this.resultResolvers.forEach((i) => {
      if (i.response || i.responseError) {
        chain.push(this.getBoundInterceptor(i.response, i), this.getBoundInterceptor(i.responseError, i));
      }
    });

    while (chain.length) {
      promise = promise.then(chain.shift(), chain.shift());
    }

    // helper success method where data is first
    promise.success = (fn) => {
      promise.then((res) => {
        fn(res.getData(), res);
      }, (e) => {
        console.warn(e);
      });
      return promise;
    };

    // helper error method where data is first
    promise.error = (fn) => {
      promise.then(null, (res) => {
        fn(res.getData(), res);
      });
      return promise;
    };

    return promise;
  }

  getBoundInterceptor(i, s) { // eslint-disable-line class-methods-use-this
    if (!i) {
      return null;
    } else if (isPlainObject(s)) {
      return i;
    }
    return bind(i, s);
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
