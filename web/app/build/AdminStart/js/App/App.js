(function() {
  define(['angular', 'DeskPRO/Main/Service/DpApi', 'AdminStart/App/AppState', 'DeskPRO/Directive/DpJsonData', 'DeskPRO/Directive/DpNgTemplate'], function(angular, DeskPRO_Main_Service_DpApi, AppState, DeskPRO_Directive_DpJsonData, DeskPRO_Directive_DpNgTemplate) {
    var AdminStartModule;
    AdminStartModule = angular.module('AdminStart_App', ['ngRoute', 'ui.bootstrap']);
    AdminStartModule.factory('dpHttpInterceptor', [
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
    AdminStartModule.config([
      '$httpProvider', function($httpProvider) {
        return $httpProvider.interceptors.push('dpHttpInterceptor');
      }
    ]);
    AdminStartModule.service('Api', [
      '$http', function($http) {
        return new DeskPRO_Main_Service_DpApi($http, window.DP_BASE_API_URL, window.DP_API_TOKEN);
      }
    ]);
    AdminStartModule.service('AppState', [
      'Api', function(Api) {
        return new AppState(Api);
      }
    ]);
    AdminStartModule.run([
      'Api', function(Api) {
        return window.setInterval(function() {
          return Api.sendGet('/my/session/renew-request-token?session_id=' + window.DP_SESSION_ID).success(function(data) {
            if (data.request_token) {
              return window.DP_REQUEST_TOKEN = data.request_token;
            }
          });
        }, 30000);
      }
    ]);
    AdminStartModule.directive('script', DeskPRO_Directive_DpJsonData);
    AdminStartModule.directive('script', DeskPRO_Directive_DpNgTemplate);
    AdminStartModule.config([
      '$routeProvider', function($routeProvider) {
        return $routeProvider.when('/', {
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Start/home.html',
          controller: 'AdminStart_Ctrl_Home'
        }).when('/cron', {
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Start/cron.html',
          controller: 'AdminStart_Ctrl_Cron'
        }).when('/email', {
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Start/email.html',
          controller: 'AdminStart_Ctrl_Email'
        }).when('/finish', {
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Start/finish.html',
          controller: 'AdminStart_Ctrl_Finish'
        }).otherwise({
          redirectTo: '/'
        });
      }
    ]);
    return AdminStartModule;
  });

}).call(this);

//# sourceMappingURL=App.js.map
