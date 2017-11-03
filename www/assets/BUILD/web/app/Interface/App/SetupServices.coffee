define [
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',
  'Reports/App/ReportsRouting',

  'Interface/App/Service/AppConfig',
  'Interface/App/Service/TemplateLoader',
  'Interface/App/Service/TemplateManager',
  'Interface/App/Service/ReportsOverview',
  'Interface/App/Service/AgentActivity',
  'Interface/App/Service/AgentHours',
  'Interface/App/Service/TicketSatisfaction',
  'Admin/Main/DataService/EntityManager',
  'Reports/App/Service/DataServiceManager',
  'Reports/App/Service/Dashboard',
  'Reports/App/Service/DashboardWidget',
  'Reports/App/Service/DashboardPermissions',
  'Reports/App/Service/DashboardsInfo',

  'Reports/Main/Service/SessionPing',
], (
  StateCollection,
  StateConfig,
  ReportsRouting

  # Services
  AppConfig,
  TemplateLoader,
  TemplateManager,
  ReportsOverview,
  AgentActivity,
  AgentHours,
  TicketSatisfaction,
  # DASHBOARDS SPECIFIC DIRECTIVES
  Admin_Main_DataService_EntityManager,
  Reports_App_Service_DataServiceManager,
  Reports_App_Service_Dashboard,
  Reports_App_Service_DashboardWidget,
  Reports_App_Service_DashboardPermissions,
  Reports_App_Service_DashboardsInfo,

  Reports_Main_Service_SessionPing,
) ->
  return (Module) ->
    Module.service('AppConfig', -> return new AppConfig)

    Module.service('TemplateLoader', [ 'AppConfig', '$http', '$q', (AppConfig, $http, $q) ->
      window.DP_TEMPLATE_LOADER = new TemplateLoader(AppConfig.getBaseUrl() + 'agent/viewer/load-views', $http, $q)
      return window.DP_TEMPLATE_LOADER
    ])

    Module.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) ->
      return new TemplateManager(TemplateLoader, $templateCache, $q)
    ])

    Module.run(['TemplateLoader', (TemplateLoader) ->
    # this is just so the loader is loaded
    ])

    Module.run(['TemplateManager', (TemplateManager) ->
      templates = [
        'ReportsInterfaceBundle:Dashboard/Modal:add-widget-variables.html',
      ]

      reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.InterfaceApp'))
      ReportsRouting(reportStates)

      # just copy paste from old-style reports
      for route in reportStates.routes
        if route.tpl
          templates.push(route.tpl)

      for t in templates
        TemplateManager.load(t)

      TemplateManager.loadPending()
    ])

    Module.factory('HttpTemplateInterceptor', [->
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

    Module.factory('dpHttpInterceptor', ['$q', ($q) ->
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
    Module.config(['$httpProvider', ($httpProvider) ->
      $httpProvider.interceptors.push('dpHttpInterceptor');
    ])

    Module.config(['$httpProvider', ($httpProvider) ->
      $httpProvider.interceptors.push('HttpTemplateInterceptor')
    ])
    Module.service('em', [ ->
      return new Admin_Main_DataService_EntityManager()
    ])
  
    Module.factory('DataService', [ '$injector', ($injector) ->
      return new Reports_App_Service_DataServiceManager($injector)
    ])
  
    Module.service('DashboardService', ['Api', '$q', (Api, $q) ->
      return new Reports_App_Service_Dashboard(Api, $q)
    ])
    Module.service('DashboardWidgetService', ['Api', '$q', (Api, $q) ->
      return new Reports_App_Service_DashboardWidget(Api, $q)
    ])
    Module.service('DashboardPermissionsService', ['Api', '$q', (Api, $q) ->
      return new Reports_App_Service_DashboardPermissions(Api, $q)
    ])
    Module.service('DashboardsInfo', ['Api', '$q', (Api, $q) ->
      return new Reports_App_Service_DashboardsInfo(Api, $q)
    ])
    Module.service('ReportsOverviewService', ['Api', '$q', (Api, $q) ->
      return new ReportsOverview(Api, $q)
    ])
    Module.service('AgentActivityService', ['Api', '$sce', (Api, $sce) ->
      return new AgentActivity(Api, $sce)
    ])
    Module.service('AgentHoursService', ['Api', '$sce', '$q', (Api, $sce, $q) ->
      return new AgentHours(Api, $sce, $q)
    ])
    Module.service('TicketSatisfactionService', ['Api', '$sce', '$q', '$timeout', (Api, $sce, $q, $timeout) ->
      return new TicketSatisfaction(Api, $sce, $q, $timeout)
    ])

    Module.service('SessionPing', ['Api', (Api) ->
      return new Reports_Main_Service_SessionPing(Api)
    ])
    Module.run(['SessionPing', (SessionPing) ->
      window.setTimeout(->
        SessionPing.startInterval()
      , 20000)
    ])
