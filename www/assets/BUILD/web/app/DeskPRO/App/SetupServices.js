// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Strings',
  'DeskPRO/Main/Service/AppState',
  'DeskPRO/Main/Service/DpApi',
  'DeskPRO/Main/Service/DpApi2',
  'DeskPRO/Main/Service/Growl',
  'DeskPRO/Main/Service/InhelpState',
], (
  Util,
  Strings,
  DeskPRO_Main_Service_AppState,
  DeskPRO_Main_Service_DpApi,
  DeskPRO_Main_Service_DpApi2,
  DeskPRO_Main_Service_Growl,
  DeskPRO_Main_Service_InhelpState,
) =>
  function(Module) {
    Module.service('AppState', ['$rootScope', '$state', ($rootScope, $state) => new DeskPRO_Main_Service_AppState($rootScope, $state)
    ]);

    Module.service('Api', ['$http', 'Growl', ($http, Growl) =>
      new DeskPRO_Main_Service_DpApi(
        $http,
        window.DP_BASE_API_URL,
        window.DP_API_TOKEN,
        Growl
      )
    
    ]);

    Module.service('Api2', ['$http', 'Growl', ($http, Growl) =>
        new DeskPRO_Main_Service_DpApi2(
          $http,
          window.DP_BASE_API_URL,
          window.DP_API_TOKEN,
          Growl
        )
      
    ]);

    Module.service('InhelpState', ['Api', Api => new DeskPRO_Main_Service_InhelpState(Api)
    ]);

    Module.service('Growl', [ () => new DeskPRO_Main_Service_Growl()
    ]);

    Module.filter('escape_url', [ () =>
      text => encodeURIComponent(text)
    
    ]);

    Module.filter('murmurhash', [ () =>
      text => Strings.murmurhash3(text)
    
    ]);

    Module.filter('filesize_display', [ () =>
      function(bytes, precision) {
        if (precision == null) { precision = 2; }
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

        const symbols = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        const exp = Math.floor(Math.log(bytes) / Math.log(1024));
        let result = bytes / Math.pow(1024, Math.floor(exp));
        result = result.toFixed(precision);

        if (symbols[exp]) { result += ` ${symbols[exp]}`; }

        return result;
      }
    
    ]);

    Module.filter('fulltime', ['$filter', $filter =>
      timestamp => $filter('date')(timestamp, 'EEEE, MMMM d, y h:mm a')
    
    ]);

    // Add fcall() to $q service (like Kris Kowal's Q: https://github.com/kriskowal/q)
    // Add isPromise
    Module.config(['$provide', $provide =>
      $provide.decorator('$q', ['$delegate', function($delegate) {
        $delegate.fcall = function(fn) {
          const d = $delegate.defer();
          d.resolve(fn());
          return d.promise;
        };

        $delegate.isPromise = val => val.then != null;

        return $delegate;
      }
      ])
    
    ]);

    return Module.config(['$provide', $provide =>
      $provide.decorator('$state', ['$delegate', '$stateParams', function($delegate, $stateParams) {
        /*
         * Checks to see if a certain state is currently active
         *
         * @param {String} stateId The state to check. If it begins with a leading dot, we'll cehck
         *                         if the id exists anywhere in the current state. E.g., shorter to write '.create' than 'x.y.z.create'
         * @param {Object} stateParams If provided, then the params specified must also match
         */
        $delegate.isStateActive = function(stateId, stateParams = null) {
          if (!$delegate.current) { return false; }

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

            if (!$delegate.$current.params) { return false; }
            for (let k of Object.keys(stateParams || {})) {
              const v = stateParams[k];
              if (($stateParams[k] == null) || ($stateParams[k] !== v)) {
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
      ])
    
    ]);
  }
);
