(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util', 'DeskPRO/Util/Strings', 'DeskPRO/Main/Service/AppState', 'DeskPRO/Main/Service/DpApi', 'DeskPRO/Main/Service/Growl', 'DeskPRO/Main/Service/InhelpState'], function(Util, Strings, DeskPRO_Main_Service_AppState, DeskPRO_Main_Service_DpApi, DeskPRO_Main_Service_Growl, DeskPRO_Main_Service_InhelpState) {
    return function(Module) {
      Module.service('AppState', [
        '$rootScope', '$state', function($rootScope, $state) {
          return new DeskPRO_Main_Service_AppState($rootScope, $state);
        }
      ]);
      Module.service('Api', [
        '$http', function($http) {
          return new DeskPRO_Main_Service_DpApi($http, window.DP_BASE_API_URL, window.DP_API_TOKEN);
        }
      ]);
      Module.service('InhelpState', [
        'Api', function(Api) {
          return new DeskPRO_Main_Service_InhelpState(Api);
        }
      ]);
      Module.service('Growl', [
        function() {
          return new DeskPRO_Main_Service_Growl();
        }
      ]);
      Module.filter('escape_url', [
        function() {
          return function(text) {
            return encodeURIComponent(text);
          };
        }
      ]);
      Module.filter('murmurhash', [
        function() {
          return function(text) {
            return Strings.murmurhash3(text);
          };
        }
      ]);
      Module.filter('filesize_display', [
        function() {
          return function(bytes, precision) {
            var exp, result, symbols;
            if (precision == null) {
              precision = 2;
            }
            if (!bytes) {
              bytes = 0;
            }
            if (!Util.isNumber(bytes)) {
              if (Util.isString(bytes)) {
                bytes = parseFloat(bytes);
                if (!bytes) {
                  bytes = 0;
                }
              } else {
                bytes = 0;
              }
            }
            if (bytes === 0) {
              return '0 B';
            }
            symbols = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            exp = Math.floor(Math.log(bytes) / Math.log(1024));
            result = bytes / Math.pow(1024, Math.floor(exp));
            result = result.toFixed(precision);
            if (symbols[exp]) {
              result += ' ' + symbols[exp];
            }
            return result;
          };
        }
      ]);
      Module.filter('fulltime', [
        '$filter', function($filter) {
          return function(timestamp) {
            return $filter('date')(timestamp, 'EEEE, MMMM d, y h:mm a');
          };
        }
      ]);
      Module.config([
        '$provide', function($provide) {
          var startRunning, stopRunning;
          window.DP_DIGEST_RUNNING = false;
          startRunning = function() {
            return window.DP_DIGEST_RUNNING = true;
          };
          stopRunning = function() {
            return window.DP_DIGEST_RUNNING = false;
          };
          return $provide.decorator('$rootScope', [
            'dpInterfaceTimer', '$delegate', function(dpInterfaceTimer, $delegate) {
              var origDigest;
              origDigest = $delegate.$digest;
              $delegate.$digest = function() {
                var ret;
                startRunning();
                dpInterfaceTimer.startDigest();
                ret = origDigest.apply($delegate, arguments);
                dpInterfaceTimer.endDigest();
                stopRunning();
                return ret;
              };
              return $delegate;
            }
          ]);
        }
      ]);
      Module.config([
        '$provide', function($provide) {
          return $provide.decorator('$q', [
            '$delegate', function($delegate) {
              $delegate.fcall = function(fn) {
                var d;
                d = $delegate.defer();
                d.resolve(fn());
                return d.promise;
              };
              $delegate.isPromise = function(val) {
                return val.then != null;
              };
              return $delegate;
            }
          ]);
        }
      ]);
      return Module.config([
        '$provide', function($provide) {
          return $provide.decorator('$state', [
            '$delegate', '$stateParams', function($delegate, $stateParams) {

              /*
              				 * Checks to see if a certain state is currently active
              				 *
              				 * @param {String} stateId The state to check. If it begins with a leading dot, we'll cehck
              				 *                         if the id exists anywhere in the current state. E.g., shorter to write '.create' than 'x.y.z.create'
              				 * @param {Object} stateParams If provided, then the params specified must also match
               */
              $delegate.isStateActive = function(stateId, stateParams) {
                var k, v;
                if (stateParams == null) {
                  stateParams = null;
                }
                if (!$delegate.current) {
                  return false;
                }
                if (stateParams) {
                  if (stateId.charAt(0) === '.') {
                    if ($delegate.current.name.indexOf(stateId) === -1) {
                      return false;
                    }
                  } else {
                    if ($delegate.current.name !== stateId) {
                      return false;
                    }
                  }
                  if (!$delegate.$current.params) {
                    return false;
                  }
                  for (k in stateParams) {
                    if (!__hasProp.call(stateParams, k)) continue;
                    v = stateParams[k];
                    if (($stateParams[k] == null) || $stateParams[k] !== v) {
                      return false;
                    }
                  }
                  return true;
                } else {
                  if (stateId.charAt(0) === '.') {
                    return $delegate.current.name.indexOf(stateId) !== -1;
                  } else {
                    return $delegate.current.name === stateId;
                  }
                }
              };
              return $delegate;
            }
          ]);
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupServices.js.map
