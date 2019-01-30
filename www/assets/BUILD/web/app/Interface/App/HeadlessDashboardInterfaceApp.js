define [
  'angular',

# Helpers
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

# angular modules
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

# global deps
  'angularGridster',

  'jquery',
  'moment',
  'momentTimezone',
], (
  angular,

  StateCollection,
  StateConfig,
  HeadlessDashboardRouting

  SetupDeskPROService,
  SetupControllers,
  SetupDirectives,
  AppConfig,
  TemplateLoader,
  TemplateManager,
  Reports_App_Service_DashboardWidget
) ->
  HeadlessDashboardInterfaceApp = angular.module('DeskPRO.HeadlessDashboardInterfaceApp', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'ui.ace',
    'ui.select2',
    'angularMoment',
    'oc.lazyLoad',
    'gridster',
  ])

  SetupDeskPROService(HeadlessDashboardInterfaceApp)

  SetupControllers(HeadlessDashboardInterfaceApp)
  SetupDirectives(HeadlessDashboardInterfaceApp)

  isDone = false
  HeadlessDashboardInterfaceApp.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
    return if isDone
    isDone = true

    $urlRouterProvider.otherwise("/")

    reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.HeadlessDashboardInterfaceApp'))
    HeadlessDashboardRouting(reportStates)
    for w in reportStates.whens
      $urlRouterProvider.when(w[0], w[1])
    for r in reportStates.routes
      r.applyToStateProvider($stateProvider)
  ])

  HeadlessDashboardInterfaceApp.service('AppConfig', -> return new AppConfig)

  HeadlessDashboardInterfaceApp.service('TemplateLoader', [ 'AppConfig', '$http', '$q', (AppConfig, $http, $q) ->
    window.DP_TEMPLATE_LOADER = new TemplateLoader(AppConfig.getBaseUrl() + 'agent/viewer/load-views', $http, $q)
    return window.DP_TEMPLATE_LOADER
  ])

  HeadlessDashboardInterfaceApp.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) ->
    return new TemplateManager(TemplateLoader, $templateCache, $q)
  ])
  HeadlessDashboardInterfaceApp.service('DashboardWidgetService', ['Api', 'Api2', '$q', (Api, Api2, $q) ->
    return new Reports_App_Service_DashboardWidget(Api, Api2, $q)
  ])

  HeadlessDashboardInterfaceApp.run(['TemplateLoader', (TemplateLoader) -> ])
  HeadlessDashboardInterfaceApp.run(['TemplateManager', (TemplateManager) -> ])

  HeadlessDashboardInterfaceApp.factory('HttpTemplateInterceptor', [->
    isTemplateUrl = (url) ->
      return !!url.replace(/^\//, '').match(/^(AgentBundle|InterfaceBundle|ReportsInterfaceBundle):/)
    getViewName = (url) ->
      return url.replace(/^\//, '')
    getLoadUrl = (view) ->
      return window.DP_TEMPLATE_LOADER.getLoadUrl([view]) + '&intercepted=1'

    return {
      request: (config) ->
        if isTemplateUrl(config.url)
          config.url = getLoadUrl(getViewName(config.url))
          config.dp_is_template = true

        return config
    }
  ])

  HeadlessDashboardInterfaceApp.config(['$httpProvider', ($httpProvider) ->
    $httpProvider.interceptors.push('HttpTemplateInterceptor')
  ])

  return HeadlessDashboardInterfaceApp