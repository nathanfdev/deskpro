define [
  'angular',

  # Services
  'Interface/App/Service/AppConfig',
  'Interface/App/Service/TemplateLoader',
  'Interface/App/Service/TemplateManager',

  # Helpers
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',

  # App routing
  'Reports/App/ReportsRouting',

  # angular modules
  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angular-moment',
  'angularOcLazyLoad',

  # global deps
  'jquery',
  'moment',
  'momentTimezone',
], (
  angular,
  AppConfig,
  TemplateLoader,
  TemplateManager,
  StateCollection,
  StateConfig,
  ReportsRouting
) ->
  InterfaceApp = angular.module('DeskPRO.InterfaceApp', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'angularMoment',
    'oc.lazyLoad'
  ])

  # TODO http://christopherthielen.github.io/ui-router-extras/#/home

  InterfaceApp.service('AppConfig', -> return new AppConfig)

  InterfaceApp.service('TemplateLoader', [ 'AppConfig', '$http', '$q', (AppConfig, $http, $q) ->
    window.DP_TEMPLATE_LOADER = new TemplateLoader(AppConfig.getBaseUrl() + 'viewer/load-views', $http, $q)
    return window.DP_TEMPLATE_LOADER
  ])

  InterfaceApp.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) ->
    return new TemplateManager(TemplateLoader, $templateCache, $q)
  ])

  InterfaceApp.run(['TemplateLoader', (TemplateLoader) ->
    # this is just so the loader is loaded
  ])
  InterfaceApp.factory('HttpTemplateInterceptor', [->
    isTemplateUrl = (url) ->
      return !!url.replace(/^\//, '').match(/^(InterfaceBundle|ReportsInterfaceBundle):/)
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

  InterfaceApp.factory('dpHttpInterceptor', ['$q', ($q) ->
    return {
      request: (config) ->
        if window.DP_SESSION_ID
          config.headers['X-DeskPRO-Session-ID'] = window.DP_SESSION_ID
        if window.DP_REQUEST_TOKEN
          config.headers['X-DeskPRO-Request-Token'] = window.DP_REQUEST_TOKEN

        return config

      response: (response) ->
        return response

      requestError: (rejection) ->
        return $q.reject(rejection)

      responseError: (rejection) ->
        return $q.reject(rejection)
    }
  ])

  ###
  # Config section
  ###
  InterfaceApp.config(['$httpProvider', ($httpProvider) ->
    $httpProvider.interceptors.push('dpHttpInterceptor');
  ])

  InterfaceApp.config(['$httpProvider', ($httpProvider) ->
    $httpProvider.interceptors.push('HttpTemplateInterceptor')
  ])

  InterfaceApp.config(['$ocLazyLoadProvider', ($ocLazyLoadProvider) ->
    $ocLazyLoadProvider.config({
      jsLoader: requirejs,
      debug: true,
      loadedModules: ['DeskPRO.InterfaceApp'],
      modules: [
        {name: 'DeskPRO.ReportsApp', files: ['Reports/ReportsApp'] }
      ]
    })
  ])

  isDone = false
  InterfaceApp.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
    return if isDone
    isDone = true

    $urlRouterProvider.otherwise("/")

    reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.ReportsApp'))
    ReportsRouting(reportStates)
    for w in reportStates.whens
      $urlRouterProvider.when(w[0], w[1])
    for r in reportStates.routes
      r.applyToStateProvider($stateProvider)
  ])

  return InterfaceApp