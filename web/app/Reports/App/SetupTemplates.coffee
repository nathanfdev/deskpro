define [
  'Reports/Main/Service/TemplateManager',
  'ReportsRouting'
], (
  Reports_Main_Service_TemplateManager,
  ReportsRouting
) ->
  return (Module) ->
    Module.service('dpTemplateManager', ['$templateCache', '$http', '$q', ($templateCache, $http, $q) ->
      return new Reports_Main_Service_TemplateManager($templateCache, $http, $q)
    ])

    # Decorate the $templateCache so view names are always the 'short' names
    # and not URLs
    # e.g.  /deskpro/reports/load-view/Index/blank.html -> Index/blank.html
    Module.config(['$provide', ($provide) ->
      $provide.decorator('$templateCache', ['$delegate', ($delegate) ->
        $delegate.ngGet = $delegate.get
        $delegate.get = (view) ->
          view = view.replace(/^.*?\/reports\/load\-view\//g, '')
          return $delegate.ngGet(view)

        $delegate.ngPut = $delegate.put
        $delegate.put = (view, value) ->
          view = view.replace(/^.*?\/reports\/load\-view\//g, '')
          return $delegate.ngPut(view, value)

        return $delegate
      ])
    ])

    # Preload templates
    Module.run(['dpTemplateManager', (dpTemplateManager) ->
      templates = [
        'Index/modal-alert.html',
        'Index/modal-confirm-leavetab.html',
        'Index/blank.html',
      ]

      for own _, route of ReportsRouting
        if route.templateName
          templates.push(route.templateName)

      for t in templates
        dpTemplateManager.load(t)

      dpTemplateManager.loadPending().then(->
        window.setTimeout(->
          window.DP_IS_BOOTED = true
        , 400)
      )
    ])