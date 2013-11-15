(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['Admin/Main/Service/AppState', 'Admin/Main/Service/DpApi', 'Admin/Main/Service/Growl', 'Admin/Main/Service/InhelpState'], function(Admin_Main_Service_AppState, Admin_Main_Service_DpApi, Admin_Main_Service_Growl, Admin_Main_Service_InhelpState) {
    return function(Module) {
      Module.service('AppState', [
        '$rootScope', '$state', function($rootScope, $state) {
          return new Admin_Main_Service_AppState($rootScope, $state);
        }
      ]);
      Module.service('Api', [
        '$http', function($http) {
          return new Admin_Main_Service_DpApi($http, window.DP_BASE_API_URL, window.DP_API_TOKEN);
        }
      ]);
      Module.service('InhelpState', [
        'Api', function(Api) {
          return new Admin_Main_Service_InhelpState(Api);
        }
      ]);
      Module.service('Growl', [
        function() {
          return new Admin_Main_Service_Growl();
        }
      ]);
      Module.filter('escape_url', [
        function() {
          return function(text) {
            return encodeURIComponent(text);
          };
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
              				# Checks to see if a certain state is currently active
              				#
              				# @param {String} stateId The state to check. If it begins with a leading dot, we'll cehck
              				#                         if the id exists anywhere in the current state. E.g., shorter to write '.create' than 'x.y.z.create'
              				# @param {Object} stateParams If provided, then the params specified must also match
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

/*
//@ sourceMappingURL=SetupServices.js.map
*/