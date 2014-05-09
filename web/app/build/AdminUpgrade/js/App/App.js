(function() {
  define(['angular', 'DeskPRO/Main/Service/DpApi'], function(angular, DeskPRO_Main_Service_DpApi) {
    var AdminUpgradeModule;
    AdminUpgradeModule = angular.module('AdminUpgrade_App', ['ngRoute']);
    AdminUpgradeModule.factory('dpHttpInterceptor', [
      '$q', function($q) {
        return {
          request: function(config) {
            if (window.DP_SESSION_ID) {
              config.headers['X-DeskPRO-Session-ID'] = window.DP_SESSION_ID;
            }
            if (window.DP_REQUEST_TOKEN) {
              config.headers['X-DeskPRO-Request-Token'] = window.DP_REQUEST_TOKEN;
            }
            return config;
          },
          response: function(response) {
            return response;
          },
          requestError: function(rejection) {
            return $q.reject(rejection);
          },
          responseError: function(rejection) {
            return $q.reject(rejection);
          }
        };
      }
    ]);
    AdminUpgradeModule.config([
      '$httpProvider', function($httpProvider) {
        return $httpProvider.interceptors.push('dpHttpInterceptor');
      }
    ]);
    AdminUpgradeModule.service('Api', [
      '$http', function($http) {
        return new DeskPRO_Main_Service_DpApi($http, window.DP_BASE_API_URL, window.DP_API_TOKEN);
      }
    ]);
    AdminUpgradeModule.config([
      '$routeProvider', function($routeProvider) {
        return $routeProvider.when('/', {
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Upgrade/home.html',
          controller: 'AdminUpgrade_Main_Ctrl_UpgradeHome'
        }).when('/progress', {
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Upgrade/watch.html',
          controller: 'AdminUpgrade_Main_Ctrl_UpgradeWatch'
        }).otherwise({
          redirectTo: '/'
        });
      }
    ]);
    return AdminUpgradeModule;
  });

}).call(this);

//# sourceMappingURL=App.js.map
