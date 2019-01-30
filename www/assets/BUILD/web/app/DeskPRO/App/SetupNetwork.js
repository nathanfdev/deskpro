// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Util'], Util =>
  function(Module) {
    Module.factory('dpHttpInterceptor', ['$q', function($q) {
      const updateTimes = [];

      // This var is used in browser tests so we can
      // properly wait for a page to be finished loading
      window.DP_AJAX_RUNNINGCOUNT = 0;
      const addRunningCount = () => window.DP_AJAX_RUNNINGCOUNT++;
      const subRunningCount = function() {
        window.DP_AJAX_RUNNINGCOUNT--;
        if (window.DP_AJAX_RUNNINGCOUNT < 0) {
          return window.DP_AJAX_RUNNINGCOUNT = 0;
        }
      };

      return {
        request(config) {
          addRunningCount();

          config.headers['X-Requested-With'] = 'XMLHttpRequest';

          if (!config.isCorsRequest) {
            if (window.DP_SESSION_ID && ((config.headers != null ? config.headers['X-DeskPRO-Session-ID'] : undefined) == null)) {
              config.headers['X-DeskPRO-Session-ID'] = window.DP_SESSION_ID;
            }
            if (window.DP_REQUEST_TOKEN && ((config.headers != null ? config.headers['X-DeskPRO-Request-Token'] : undefined) == null)) {
              config.headers['X-DeskPRO-Request-Token'] = window.DP_REQUEST_TOKEN;
            }

            if ((config.headers != null ? config.headers['X-DeskPRO-API-Token'] : undefined) != null) {
              config.startTime = new Date();

              const next = updateTimes.pop();
              if (next) {
                if (config.url.indexOf('?') === -1) {
                  config.url += '?';
                } else {
                  config.url += '&';
                }
              }
            }

                //timeEnc = ((next.timeTaken / 1000) + "").replace(/\./, '_')
                //config.url += "__dp_reqtime=#{next.requestId}_t#{timeEnc}"

            config.url = config.url.replace(/^DP_URL\//g, window.DP_BASE_URL.replace(/\/+$/, '')+'/');
          }

          return config;
        },

        response(response) {
          subRunningCount();
          if (response.config.startTime) {
            const headers = response.headers();
            if (headers['x-deskpro-requestid'] != null) {
              const lastRequestId = headers['x-deskpro-requestid'];
              const lastRequestTime = ((new Date()).getTime()) - response.config.startTime.getTime();

              updateTimes.push({
                timeTaken: lastRequestTime,
                requestId: lastRequestId
              });
            }
          }
          return response;
        },

        requestError(rejection) {
          subRunningCount();
          return $q.reject(rejection);
        },

        responseError(rejection) {
          subRunningCount();
          return $q.reject(rejection);
        }
      };
    }
    ]);

    Module.config(['$httpProvider', 'fileUploadProvider', function($httpProvider, fileUploadProvider) {
      $httpProvider.interceptors.push('dpHttpInterceptor');

      return angular.extend(fileUploadProvider.defaults, {
        headers: {'X-DeskPRO-API-Token': window.DP_API_TOKEN}
      });
    }
    ]);

    return Module.config(['$provide', $provide =>
      $provide.decorator('$http', function($delegate) {

        var formatUrlObject = function(obj, baseName) {
          if (baseName == null) { baseName = false; }
          let url = '';
          return (() => {
            const result = [];
            for (let k of Object.keys(obj || {})) {
              let v = obj[k];
              if (v === null) { continue; }
              if (baseName) {
                k = baseName + '[' + encodeURIComponent(k) + ']';
              } else {
                k = encodeURIComponent(k);
              }

              if (Util.isObject(v)) {
                result.push(url += formatUrlObject(v, k));
              } else {
                v = encodeURIComponent(v);
                result.push(url += `${k}=${v}&`);
              }
            }
            return result;
          })();
        };

        $delegate.formatApiUrl = function(endpoint, params, signed) {
          if (signed == null) { signed = true; }
          endpoint = endpoint.replace(/^\//, '');
          let url = `${window.DP_BASE_API_URL}/${endpoint}`;

          if (params) {
            url += url.indexOf('?') === -1 ? '?' : '&';
            if (Util.isArray(params)) {
              for (let itm of Array.from(params)) {
                const k = encodeURIComponent(itm.name);
                const v = encodeURIComponent(itm.value);
                url += `${k}=${v}&`;
              }
            } else {
              url += formatUrlObject(params);
            }
          }

          url = url.replace(/&$/, '');

          if (signed) { url = this.signUrl(url); }

          return url;
        };

        $delegate.formatApi2Url = function(endpoint, params) {
          endpoint = endpoint.replace(/^\//, '');
          let url = `${window.DP_BASE_API_URL}/v2/${endpoint}`;

          if (params) {
            url += url.indexOf('?') === -1 ? '?' : '&';
            if (Util.isArray(params)) {
              for (let itm of Array.from(params)) {
                const k = encodeURIComponent(itm.name);
                const v = encodeURIComponent(itm.value);
                url += `${k}=${v}&`;
              }
            } else {
              url += formatUrlObject(params);
            }
          }

          url = url.replace(/&$/, '');

          return url;
        };

        $delegate.signUrl = function(url) {
          url += url.indexOf('?') === -1 ? '?' : '&';
          url += `API-TOKEN=${window.DP_API_TOKEN}&SESSION-ID=${window.DP_SESSION_ID}&REQUEST-TOKEN=${window.DP_REQUEST_TOKEN}`;
          return url;
        };

        return $delegate;
      })
    
    ]);
  }
);