define ->
  class StateConfig
    @createFactory: (module) ->
      return (id = null) ->
        c = new StateConfig(module, id)
        return c

    constructor: (@module = '', @id = null, @url = '', @ctrl = null, @tpl = null, @resolve = null, @options = null) ->
    setModule:  (@module) -> this
    setId:      (@id) -> this
    setUrl:     (@url) -> this
    setCtrl:    (@ctrl) -> this
    setTpl:     (@tpl) -> this
    setResolve: (@resolve) -> this
    setOptions: (@options) -> this
    setAbstract: ->
      @is_abstract = true
      this

    applyToStateProvider: ($stateProvider) ->
      resolve = @resolve || {}
      options = @options || {}

      m = @module

      resolve.loadModule = ['$ocLazyLoad', ($ocLazyLoad) ->
        return $ocLazyLoad.load(m)
      ]

      options.url         = @url
      options.templateUrl = @tpl
      options.resolve     = resolve
      options.controller  = @ctrl

      if @isAbstract
        options.abstract = true

      $stateProvider.state(@id, options)
