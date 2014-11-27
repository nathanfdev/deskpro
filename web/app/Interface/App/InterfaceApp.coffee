define [
  'angular',

  # Services
  'Interface/App/Service/AppConfig',
  'Interface/App/Service/StateConfig',
  'Interface/App/Service/TemplateLoader',
  'Interface/App/Service/TemplateManager',

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
  StateConfig,
  TemplateLoader,
  TemplateManager
) ->
  InterfaceApp = angular.module('DeskPRO.InterfaceApp', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'angularMoment',
    'oc.lazyLoad'
  ])

  InterfaceApp.service('AppConfig', -> return new AppConfig)

  InterfaceApp.service('StateConfig', ['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
    new StateConfig($stateProvider, $urlRouterProvider)
  ])

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

  InterfaceApp.config(['$httpProvider', ($httpProvider) ->
      $httpProvider.interceptors.push('HttpTemplateInterceptor')
  ])

  InterfaceApp.config(['$ocLazyLoadProvider', ($ocLazyLoadProvider) ->
    $ocLazyLoadProvider.config({
      jsLoader: require,
      debug: true,
      loadedModules: ['DeskPRO.InterfaceApp']
    })
  ])

  InterfaceApp.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
    $urlRouterProvider.otherwise("/loading")
    $stateProvider.state('app_loading', {
      url: "/loading",
      templateUrl: "InterfaceBundle:Interface:main-loader.html",
    })
  ])

  return InterfaceApp