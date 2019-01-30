/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'angular',

// Helpers
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',

  'Interface/App/Routing/HeadlessDashboardRouting',
  'DeskPRO/App/SetupServices',
  'Interface/App/SetupControllers',
  'Interface/App/SetupDirectives',
  'Interface/App/Service/AppConfig',
  'Interface/App/Service/TemplateLoader',
  'Interface/App/Service/TemplateManager',
  'Reports/App/Service/DashboardWidget',

// angular modules
  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angularUiAce',
  'angular-moment',
  'angularOcLazyLoad',
  'aceEditor',

  'ngFileUpload',
  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',

// global deps
  'angularGridster',

  'jquery',
  'moment',
  'momentTimezone',
], function(
  angular,

  StateCollection,
  StateConfig,
  HeadlessDashboardRouting,

  SetupDeskPROService,
  SetupControllers,
  SetupDirectives,
  AppConfig,
  TemplateLoader,
  TemplateManager,
  Reports_App_Service_DashboardWidget
) {
  const HeadlessDashboardInterfaceApp = angular.module('DeskPRO.HeadlessDashboardInterfaceApp', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'ui.ace',
    'ui.select2',
    'angularMoment',
    'oc.lazyLoad',
    'gridster',
  ]);

  SetupDeskPROService(HeadlessDashboardInterfaceApp);

  SetupControllers(HeadlessDashboardInterfaceApp);
  SetupDirectives(HeadlessDashboardInterfaceApp);

  let isDone = false;
  HeadlessDashboardInterfaceApp.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
    if (isDone) { return; }
    isDone = true;

    $urlRouterProvider.otherwise("/");

    const reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.HeadlessDashboardInterfaceApp'));
    HeadlessDashboardRouting(reportStates);
    for (let w of Array.from(reportStates.whens)) {
      $urlRouterProvider.when(w[0], w[1]);
    }
    return Array.from(reportStates.routes).map((r) =>
      r.applyToStateProvider($stateProvider));
  }
  ]);

  HeadlessDashboardInterfaceApp.service('AppConfig', () => new AppConfig);

  HeadlessDashboardInterfaceApp.service('TemplateLoader', [ 'AppConfig', '$http', '$q', function(AppConfig, $http, $q) {
    window.DP_TEMPLATE_LOADER = new TemplateLoader(AppConfig.getBaseUrl() + 'agent/viewer/load-views', $http, $q);
    return window.DP_TEMPLATE_LOADER;
  }
  ]);

  HeadlessDashboardInterfaceApp.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) => new TemplateManager(TemplateLoader, $templateCache, $q)
  ]);
  HeadlessDashboardInterfaceApp.service('DashboardWidgetService', ['Api', 'Api2', '$q', (Api, Api2, $q) => new Reports_App_Service_DashboardWidget(Api, Api2, $q)
  ]);

  HeadlessDashboardInterfaceApp.run(['TemplateLoader', function(TemplateLoader) {} ]);
  HeadlessDashboardInterfaceApp.run(['TemplateManager', function(TemplateManager) {} ]);

  HeadlessDashboardInterfaceApp.factory('HttpTemplateInterceptor', [function() {
    const isTemplateUrl = url => !!url.replace(/^\//, '').match(/^(AgentBundle|InterfaceBundle|ReportsInterfaceBundle):/);
    const getViewName = url => url.replace(/^\//, '');
    const getLoadUrl = view => window.DP_TEMPLATE_LOADER.getLoadUrl([view]) + '&intercepted=1';

    return {
      request(config) {
        if (isTemplateUrl(config.url)) {
          config.url = getLoadUrl(getViewName(config.url));
          config.dp_is_template = true;
        }

        return config;
      }
    };
  }
  ]);

  HeadlessDashboardInterfaceApp.config(['$httpProvider', $httpProvider => $httpProvider.interceptors.push('HttpTemplateInterceptor')
  ]);

  return HeadlessDashboardInterfaceApp;
});