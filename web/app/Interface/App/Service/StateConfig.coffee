define ->
  class StateConfig
    constructor: (@$stateProvider, @$urlRouterProvider) ->

    addState: (module, id, url, ctrl, tpl, resolve, options) ->
      resolve = resolve || {}
      options = options || {}

      resolve.loadModule = ['$ocLazyLoad', ($ocLazyLoad) ->
        $ocLazyLoad.load({
          name: module,
          files: [module]
        })
      ]

      options.url         = url
      options.templateUrl = tpl
      options.resolve     = resolve

      return $stateProvider.state(id, options)