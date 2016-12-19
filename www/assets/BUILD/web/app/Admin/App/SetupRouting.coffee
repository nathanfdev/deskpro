define [
  'AdminRouting',
  'Admin/Cloud/App/RouteMutator'
], (
  AdminRouting,
  Admin_Cloud_App_RouteMutator
) ->
  return (Module) ->
    Module.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
      $urlRouterProvider.otherwise("/")

      # Load templates through the dpTemplateManager
      # so we can take advantage of our preloading scheme
      makeProvider = (view) ->
        return ['dpTemplateManager', (dpTemplateManager) ->
          return dpTemplateManager.get(view)
        ]

      if window.DP_IS_CLOUD
        routeMutator = new Admin_Cloud_App_RouteMutator()

      procRoute = (route) ->
        id = route.id
        url = route.url

        if route.templateName?
          route.templateProvider = makeProvider(route.templateName)

        opts = {
          url: url,
          data: route.data || null
        }

        if route.resolve
          opts.resolve = route.resolve

        if route.views
          opts.views = route.views
        else if route.abstract
          opts.abstract = true
          opts.template = '<ui-view/>'
        else
          opts.views = {}

          v = {}
          if route.templateProvider
            v.templateProvider = route.templateProvider
          else if route.templateName
            v.templateName = route.templateName
          if route.controller
            v.controller = route.controller

          if route.target
            viewName = route.target
          else
            # An app-level (tickets.ticket_deps)
            # Is always added to the appbody
            if id.split('.').length == 2
              viewName = "appbody"
            else
              viewName = ""

          opts.views[viewName] = v

        $stateProvider.state(id, opts)

      for route in AdminRouting
        if routeMutator
          route = routeMutator.processRoute(route)
          continue if not route

        procRoute(route)

      if routeMutator
        for route in routeMutator.getExtraRoutes()
          procRoute(route)
    ])