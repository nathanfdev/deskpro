(function() {
  define(function() {
    return function(Module) {
      Module.factory('dpHttpInterceptor', [
        function() {
          var updateTimes;
          updateTimes = [];
          return {
            request: function(config) {
              var next, timeEnc, _ref;
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
              return rejection;
            },
            responseError: function(rejection) {
              return rejection;
            }
          };
        }
      ]);
      return Module.config([
        '$httpProvider', function($httpProvider) {
          return $httpProvider.interceptors.push('dpHttpInterceptor');
        }
      ]);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupNetwork.js.map
*/