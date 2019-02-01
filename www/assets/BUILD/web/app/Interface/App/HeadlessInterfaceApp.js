define([
  'angular',

// Helpers
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',

  'Interface/App/Routing/HeadlessRouting',
  'DeskPRO/App/SetupServices',
  'Interface/App/SetupControllers',
  'Interface/App/SetupDirectives',
  'Interface/App/Service/AppConfig',
  'Interface/App/Service/TemplateLoader',
  'Interface/App/Service/TemplateManager',

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
  'momentTimezone'
], (
  angular,

  StateCollection,
  StateConfig,
  HeadlessRouting,

  SetupDeskPROService,
  SetupControllers,
  SetupDirectives,
  AppConfig,
  TemplateLoader,
  TemplateManager
) => {
  const HeadlessInterfaceApp = angular.module('DeskPRO.HeadlessInterfaceApp', [
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

  SetupDeskPROService(HeadlessInterfaceApp);

  SetupControllers(HeadlessInterfaceApp);
  SetupDirectives(HeadlessInterfaceApp);

  let isDone = false;
  HeadlessInterfaceApp.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider) {
    if (isDone) { return; }
    isDone = true;

    $urlRouterProvider.otherwise('/');

    const reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.HeadlessInterfaceApp'));
    HeadlessRouting(reportStates);
    for (const w of Array.from(reportStates.whens)) {
      $urlRouterProvider.when(w[0], w[1]);
    }
    return Array.from(reportStates.routes).map(r =>
      r.applyToStateProvider($stateProvider));
  }
  ]);

  HeadlessInterfaceApp.service('AppConfig', () => new AppConfig());

  HeadlessInterfaceApp.service('TemplateLoader', ['AppConfig', '$http', '$q', function (AppConfig, $http, $q) {
    window.DP_TEMPLATE_LOADER = new TemplateLoader(`${AppConfig.getBaseUrl()}agent/viewer/load-views`, $http, $q);
    return window.DP_TEMPLATE_LOADER;
  }
  ]);

  HeadlessInterfaceApp.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) => new TemplateManager(TemplateLoader, $templateCache, $q)
  ]);

  HeadlessInterfaceApp.run(['TemplateLoader', function (TemplateLoader) {}]);
  HeadlessInterfaceApp.run(['TemplateManager', function (TemplateManager) {}]);

  HeadlessInterfaceApp.factory('HttpTemplateInterceptor', [function () {
    const isTemplateUrl = url => !!url.replace(/^\//, '').match(/^(AgentBundle|InterfaceBundle|ReportsInterfaceBundle):/);
    const getViewName = url => url.replace(/^\//, '');
    const getLoadUrl = view => `${window.DP_TEMPLATE_LOADER.getLoadUrl([view])}&intercepted=1`;

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

  HeadlessInterfaceApp.config(['$httpProvider', $httpProvider => $httpProvider.interceptors.push('HttpTemplateInterceptor')
  ]);

  HeadlessInterfaceApp.service('DashboardWidgetService', [function () {}]);

  return HeadlessInterfaceApp;
});
