(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    return function(Module) {
      Module.factory('dpHttpInterceptor', [
        '$q', function($q) {
          var addRunningCount, subRunningCount, updateTimes;
          updateTimes = [];
          window.DP_AJAX_RUNNINGCOUNT = 0;
          addRunningCount = function() {
            return window.DP_AJAX_RUNNINGCOUNT++;
          };
          subRunningCount = function() {
            window.DP_AJAX_RUNNINGCOUNT--;
            if (window.DP_AJAX_RUNNINGCOUNT < 0) {
              return window.DP_AJAX_RUNNINGCOUNT = 0;
            }
          };
          return {
            request: function(config) {
              var next, _ref, _ref1, _ref2;
              addRunningCount();
              if (window.DP_SESSION_ID && (((_ref = config.headers) != null ? _ref['X-DeskPRO-Session-ID'] : void 0) == null)) {
                config.headers['X-DeskPRO-Session-ID'] = window.DP_SESSION_ID;
              }
              if (window.DP_REQUEST_TOKEN && (((_ref1 = config.headers) != null ? _ref1['X-DeskPRO-Request-Token'] : void 0) == null)) {
                config.headers['X-DeskPRO-Request-Token'] = window.DP_REQUEST_TOKEN;
              }
              if (((_ref2 = config.headers) != null ? _ref2['X-DeskPRO-API-Token'] : void 0) != null) {
                config.startTime = new Date();
                next = updateTimes.pop();
                if (next) {
                  if (config.url.indexOf('?') === -1) {
                    config.url += '?';
                  } else {
                    config.url += '&';
                  }
                }
              }
              config.url = config.url.replace(/^DP_URL\//g, window.DP_BASE_URL.replace(/\/+$/, '') + '/');
              return config;
            },
            response: function(response) {
              var headers, lastRequestId, lastRequestTime;
              subRunningCount();
              if (response.config.startTime) {
                headers = response.headers();
                if (headers['x-deskpro-requestid'] != null) {
                  lastRequestId = headers['x-deskpro-requestid'];
                  lastRequestTime = ((new Date()).getTime()) - response.config.startTime.getTime();
                  updateTimes.push({
                    timeTaken: lastRequestTime,
                    requestId: lastRequestId
                  });
                }
              }
              return response;
            },
            requestError: function(rejection) {
              subRunningCount();
              return $q.reject(rejection);
            },
            responseError: function(rejection) {
              subRunningCount();
              return $q.reject(rejection);
            }
          };
        }
      ]);
      Module.config([
        '$httpProvider', 'fileUploadProvider', function($httpProvider, fileUploadProvider) {
          $httpProvider.interceptors.push('dpHttpInterceptor');
          return angular.extend(fileUploadProvider.defaults, {
            headers: {
              'X-DeskPRO-API-Token': window.DP_API_TOKEN
            }
          });
        }
      ]);
      return Module.config([
        '$provide', function($provide) {
          return $provide.decorator('$http', function($delegate) {
            $delegate.formatApiUrl = function(endpoint, params, signed) {
              var itm, k, url, v, _i, _len;
              if (signed == null) {
                signed = true;
              }
              endpoint = endpoint.replace(/^\//, '');
              url = "" + window.DP_BASE_API_URL + "/" + endpoint;
              if (params) {
                url += url.indexOf('?') === -1 ? '?' : '&';
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
              if (signed) {
                url = this.signUrl(url);
              }
              return url;
            };
            $delegate.signUrl = function(url) {
              url += url.indexOf('?') === -1 ? '?' : '&';
              url += 'API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN;
              return url;
            };
            return $delegate;
          });
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupNetwork.js.map
