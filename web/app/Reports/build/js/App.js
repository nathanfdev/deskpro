(function() {
  define(['angular', 'DP_LANG', 'ReportsRouting', 'Admin/Main/Service/DpApi', 'Admin/Main/Service/Growl', 'Admin/Main/Service/TemplateManager', 'Admin/Main/Translate/DpInterpolation'], function(angular, DP_LANG, routing, Admin_Main_Service_DpApi, Admin_Main_Service_Growl, Admin_Main_Service_TemplateManager, Admin_Main_Translate_DpInterpolation) {
    var Reports_App, _ref, _ref1;
    Reports_App = angular.module('Reports_App', ['ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable', 'pascalprecht.translate']);
    Reports_App.service('Api', [
      '$http', function($http) {
        return new Admin_Main_Service_DpApi($http, window.DP_BASE_API_URL, window.DP_API_TOKEN);
      }
    ]);
    Reports_App.factory('$exceptionHandler', [
      '$log', function($log) {
        return function(exception, cause) {
          throw exception;
        };
      }
    ]);
    Reports_App.filter('escape_url', [
      function() {
        return function(text) {
          return encodeURIComponent(text);
        };
      }
    ]);
    Reports_App.service('Growl', [
      function() {
        return new Admin_Main_Service_Growl();
      }
    ]);
    Reports_App.factory('translateDpInterpolation', Admin_Main_Translate_DpInterpolation);
    Reports_App.config([
      '$translateProvider', function($translateProvider) {
        $translateProvider.translations('default', DP_LANG);
        $translateProvider.preferredLanguage('default');
        return $translateProvider.useInterpolation('translateDpInterpolation');
      }
    ]);
    Reports_App.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {}]);
    Reports_App.service('dpTemplateManager', [
      '$templateCache', '$http', '$q', function($templateCache, $http, $q) {
        return new Admin_Main_Service_TemplateManager($templateCache, $http, $q);
      }
    ]);
    Reports_App.config([
      '$provide', function($provide) {
        return $provide.decorator('$templateCache', [
          '$delegate', '$http', function($delegate, $http) {
            $delegate.ngGet = $delegate.get;
            $delegate.get = function(view) {
              view = view.replace(/^.*?\/adm\/load\-view\//g, '');
              return $delegate.ngGet(view);
            };
            $delegate.ngPut = $delegate.put;
            $delegate.put = function(view, value) {
              view = view.replace(/^.*?\/adm\/load\-view\//g, '');
              return $delegate.ngPut(view, value);
            };
            return $delegate;
          }
        ]);
      }
    ]);
    if ((_ref = window.parent) != null ? _ref.DP_FRAME_OVERLAY_reports : void 0) {
      if ((_ref1 = window.parent) != null) {
        _ref1.DP_FRAME_OVERLAY_reports.callLoaded();
      }
    }
    return Reports_App;
  });

}).call(this);

/*
//@ sourceMappingURL=App.js.map
*/