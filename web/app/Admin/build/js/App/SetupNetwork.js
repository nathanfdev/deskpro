(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    return function(Module) {
      Module.factory('dpHttpInterceptor', [
        '$q', function($q) {
          var addRunningCount, subCounter, subRunningCount, subTimeout, updateTimes;
          updateTimes = [];
          window.DP_AJAX_RUNNINGCOUNT = 0;
          subTimeout = null;
          subCounter = 0;
          addRunningCount = function() {
            return window.DP_AJAX_RUNNINGCOUNT++;
          };
          subRunningCount = function() {
            subCounter++;
            if (!subTimeout) {
              return subTimeout = setTimeout(function() {
                subTimeout = null;
                window.DP_AJAX_RUNNINGCOUNT -= subCounter;
                return subCounter = 0;
              }, 250);
            }
          };
          return {
            request: function(config) {
              var next, timeEnc, _ref;
              addRunningCount();
              if (((_ref = config.headers) != null ? _ref['X-DeskPRO-API-Token'] : void 0) != null) {
                config.startTime = new Date();
                next = updateTimes.pop();
                if (next) {
                  if (config.url.indexOf('?') === -1) {
                    config.url += '?';
                  } else {
                    config.url += '&';
                  }
                  timeEnc = ((next.timeTaken / 1000) + "").replace(/\./, '_');
                  config.url += "__dp_reqtime=" + next.requestId + "_t" + timeEnc;
                }
              }
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
      return Module.config([
        '$httpProvider', 'fileUploadProvider', function($httpProvider, fileUploadProvider) {
          $httpProvider.interceptors.push('dpHttpInterceptor');
          return angular.extend(fileUploadProvider.defaults, {
            headers: {
              'X-DeskPRO-API-Token': window.DP_API_TOKEN
            }
          });
        }
      ]);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupNetwork.js.map
*/