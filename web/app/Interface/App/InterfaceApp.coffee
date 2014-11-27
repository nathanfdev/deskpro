define [
  'angular',

  # Services
  'Interface/App/Service/AppConfig',
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
    $stateProvider.state('app', {
      url: "/",
      templateUrl: "InterfaceBundle:Interface:main-frame.html",
      controller: [ '$state', ($state) ->
        $state.go('app.reports')
      ]
    })

    addState = (module, id, url, ctrl, tpl, resolve, options) ->
      resolve = resolve || {}
      options = options || {}

      resolve.loadModule = ['$ocLazyLoad', ($ocLazyLoad) ->
        return $ocLazyLoad.load(module)
      ]

      options.url         = url
      options.templateUrl = tpl
      options.resolve     = resolve

      return $stateProvider.state(id, options)

    # TODO: make these dynamic somehow based on loaded apps
    addState('DeskPRO.ReportsApp', 'app.reports', 'reports', 'Reports.App.Main', 'ReportsInterfaceBundle:Interface:main.html')
  ])

  return InterfaceApp