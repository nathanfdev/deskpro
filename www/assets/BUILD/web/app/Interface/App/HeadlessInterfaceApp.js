define [
  'angular',

# Helpers
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',

  'Interface/App/Routing/HeadlessRouting',
  'DeskPRO/App/SetupServices',
  'Interface/App/SetupControllers',
  'Interface/App/SetupDirectives',
  'Interface/App/Service/AppConfig',
  'Interface/App/Service/TemplateLoader',
  'Interface/App/Service/TemplateManager',

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
  HeadlessRouting

  SetupDeskPROService,
  SetupControllers,
  SetupDirectives,
  AppConfig,
  TemplateLoader,
  TemplateManager,
) ->
  HeadlessInterfaceApp = angular.module('DeskPRO.HeadlessInterfaceApp', [
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

  SetupDeskPROService(HeadlessInterfaceApp)

  SetupControllers(HeadlessInterfaceApp)
  SetupDirectives(HeadlessInterfaceApp)

  isDone = false
  HeadlessInterfaceApp.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
    return if isDone
    isDone = true

    $urlRouterProvider.otherwise("/")

    reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.HeadlessInterfaceApp'))
    HeadlessRouting(reportStates)
    for w in reportStates.whens
      $urlRouterProvider.when(w[0], w[1])
    for r in reportStates.routes
      r.applyToStateProvider($stateProvider)
  ])

  HeadlessInterfaceApp.service('AppConfig', -> return new AppConfig)

  HeadlessInterfaceApp.service('TemplateLoader', [ 'AppConfig', '$http', '$q', (AppConfig, $http, $q) ->
    window.DP_TEMPLATE_LOADER = new TemplateLoader(AppConfig.getBaseUrl() + 'agent/viewer/load-views', $http, $q)
    return window.DP_TEMPLATE_LOADER
  ])

  HeadlessInterfaceApp.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) ->
    return new TemplateManager(TemplateLoader, $templateCache, $q)
  ])

  HeadlessInterfaceApp.run(['TemplateLoader', (TemplateLoader) -> ])
  HeadlessInterfaceApp.run(['TemplateManager', (TemplateManager) -> ])

  HeadlessInterfaceApp.factory('HttpTemplateInterceptor', [->
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

  HeadlessInterfaceApp.config(['$httpProvider', ($httpProvider) ->
    $httpProvider.interceptors.push('HttpTemplateInterceptor')
  ])

  HeadlessInterfaceApp.service('DashboardWidgetService', [() -> ])

  return HeadlessInterfaceApp