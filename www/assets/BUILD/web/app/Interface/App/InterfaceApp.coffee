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
  'DeskPRO/App/SetupServices',

  # Controllers
  'Reports/Dashboards/Ctrl/DashboardReport',
  'Reports/Dashboards/Ctrl/DashboardView',

  # Modal controllers
  'Reports/Dashboards/ModalCtrl/EditDashboard',
  'Reports/Dashboards/ModalCtrl/ChooseWidget',
  'Reports/Dashboards/ModalCtrl/AddWidget',
  'Reports/Dashboards/ModalCtrl/EditWidget',

  # Stats controllers
  'Reports/Stats/Ctrl/StatsMain',
  'Reports/Stats/Ctrl/StatsHome',
  'Reports/Stats/Ctrl/WidgetView',

  # Legacy
  'Reports/AgentActivity/Ctrl/AgentActivity',
  'Reports/AgentHours/Ctrl/AgentHours',
  'Reports/Builder/Ctrl/Edit',
  'Reports/Builder/Ctrl/List',
  'Reports/TicketSatisfaction/Ctrl/TicketSatisfaction',

  # STANDARD SERVICES
  'Reports/App/Service/Dashboard',
  'Reports/App/Service/DashboardWidget',
  'Reports/App/Service/DashboardPermissions',
  'Reports/App/Service/DashboardsInfo',

  # DASHBOARDS SPECIFIC DIRECTIVES
  'Reports/Dashboards/Directive/DashboardAmcharts',
  'Reports/Dashboards/Directive/DashboardStat',
  'Reports/Dashboards/Directive/DashboardTable',
  'Reports/Dashboards/Directive/DpReportVariables',

  # LEGACY DIRECTIVES
  'Reports/Directive/DpReportBuilderSelectBox',
  'Reports/Directive/DpReportBillingSelectBox',
  'Reports/Directive/DpReportBuilderTitle',

  # DP DIRECTIVES
  'DeskPRO/Directive/DpDropdown',
  'DeskPRO/Directive/DpShowSpinning',
  'DeskPRO/Directive/DpHideSpinning',
  'DeskPRO/Directive/DpTabBody',
  'DeskPRO/Directive/DpTabBtn',

  # angular modules
  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angular-moment',
  'angularOcLazyLoad',

  'ngFileUpload',
  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',

  # global deps
  'angularGridster',
  'ngTable',
  'amcharts',
  'amcharts.pie',
  'amcharts.serial',
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

  SetupDeskPROService,

  # Controllers
  Reports_Dashboards_Ctrl_DashboardReport,
  Reports_Dashboards_Ctrl_DashboardView,

  # Modal controllers
  Reports_Dashboards_ModalCtrl_EditDashboard,
  Reports_Dashboards_ModalCtrl_ChooseWidget,
  Reports_Dashboards_ModalCtrl_AddWidget,
  Reports_Dashboards_ModalCtrl_EditWidget,

  # Stats controller
  Reports_Stats_Ctrl_StatsMain,
  Reports_Stats_Ctrl_StatsHome,
  Reports_Stats_Ctrl_WidgetView,

  # Legacy
  Reports_AgentActivity_Ctrl_AgentActivity,
  Reports_AgentHours_Ctrl_AgentHours,
  Reports_Builder_Ctrl_Edit,
  Reports_Builder_Ctrl_List,
  Reports_TicketSatisfaction_Ctrl_TicketSatisfaction,

  Reports_App_Service_Dashboard,
  Reports_App_Service_DashboardWidget,
  Reports_App_Service_DashboardPermissions,
  Reports_App_Service_DashboardsInfo,

  # DASHBOARDS SPECIFIC DIRECTIVES
  Reports_Dashboards_Directive_DashboardAmcharts,
  Reports_Dashboards_Directive_DashboardStat,
  Reports_Dashboards_Directive_DashboardTable,
  Reports_Dashboards_Directive_DpReportVariables,

  Reports_Directive_DpReportBuilderSelectBox,
  Reports_Directive_DpReportBillingSelectBox,
  Reports_Directive_DpReportBuilderTitle,

  # DP DIRECTIVES
  Reports_App_Directive_DpDropdown,
  Reports_App_Directive_DpShowSpinning,
  Reports_App_Directive_DpHideSpinning,
  Reports_App_Directive_DpTabBody,
  Reports_App_Directive_DpTabBtn,

) ->
  InterfaceApp = angular.module('DeskPRO.InterfaceApp', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'angularMoment',
    'oc.lazyLoad',
    'gridster',
  ])

  # TODO http://christopherthielen.github.io/ui-router-extras/#/home

  InterfaceApp.service('AppConfig', -> return new AppConfig)

  InterfaceApp.service('TemplateLoader', [ 'AppConfig', '$http', '$q', (AppConfig, $http, $q) ->
    window.DP_TEMPLATE_LOADER = new TemplateLoader(AppConfig.getBaseUrl() + 'reports/viewer/load-views', $http, $q)
    return window.DP_TEMPLATE_LOADER
  ])

  InterfaceApp.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) ->
    return new TemplateManager(TemplateLoader, $templateCache, $q)
  ])

  InterfaceApp.run(['TemplateLoader', (TemplateLoader) ->
    # this is just so the loader is loaded
  ])
  SetupDeskPROService(InterfaceApp)
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

  ###
  # Controllers section
  ###
  InterfaceApp.controller('Reports.App.DashboardReport',             Reports_Dashboards_Ctrl_DashboardReport)
  InterfaceApp.controller('Reports.App.DashboardView',               Reports_Dashboards_Ctrl_DashboardView)
  InterfaceApp.controller('Reports.Dashboards.Modals.EditDashboard', Reports_Dashboards_ModalCtrl_EditDashboard)
  InterfaceApp.controller('Reports.Dashboards.Modals.ChooseWidget',  Reports_Dashboards_ModalCtrl_ChooseWidget)
  InterfaceApp.controller('Reports.Dashboards.Modals.AddWidget',     Reports_Dashboards_ModalCtrl_AddWidget)
  InterfaceApp.controller('Reports.Dashboards.Modals.EditWidget',    Reports_Dashboards_ModalCtrl_EditWidget)

  InterfaceApp.controller('Reports.Stats.StatsMain',                 Reports_Stats_Ctrl_StatsMain)
  InterfaceApp.controller('Reports.Stats.StatsHome',                 Reports_Stats_Ctrl_StatsHome)
  InterfaceApp.controller('Reports.Stats.WidgetView',                Reports_Stats_Ctrl_WidgetView)


  ###
  # Services section
  ###
  InterfaceApp.service('DashboardService', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_Dashboard(Api, $q)
  ])
  InterfaceApp.service('DashboardWidgetService', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_DashboardWidget(Api, $q)
  ])
  InterfaceApp.service('DashboardPermissionsService', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_DashboardPermissions(Api, $q)
  ])
  InterfaceApp.service('DashboardsInfo', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_DashboardsInfo(Api, $q)
  ])

  ###
  # Directives section
  ###

  ###
  # Dashboards specific directives
  ###
  InterfaceApp.directive('dashboardAmcharts',         Reports_Dashboards_Directive_DashboardAmcharts)
  InterfaceApp.directive('dashboardTable',            Reports_Dashboards_Directive_DashboardTable)
  InterfaceApp.directive('dpReportVariables',         Reports_Dashboards_Directive_DpReportVariables)

  ###
  # Legacy directives
  ###
  InterfaceApp.directive('dpReportBuilderSelectBox',       Reports_Directive_DpReportBuilderSelectBox)
  InterfaceApp.directive('dpReportBillingSelectBox',       Reports_Directive_DpReportBillingSelectBox)
  InterfaceApp.directive('dpReportBuilderTitle',           Reports_Directive_DpReportBuilderTitle)

  ###
  # DeskPRO directives
  ###
  InterfaceApp.directive('dpDropdown',                Reports_App_Directive_DpDropdown)
  InterfaceApp.directive('dpShowSpinning',                 Reports_App_Directive_DpShowSpinning)
  InterfaceApp.directive('dpHideSpinning',                 Reports_App_Directive_DpHideSpinning)
  InterfaceApp.directive('dpTabBody',                      Reports_App_Directive_DpTabBody)
  InterfaceApp.directive('dpTabBtn',                       Reports_App_Directive_DpTabBtn)


  isDone = false
  InterfaceApp.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
    return if isDone
    isDone = true

    $urlRouterProvider.otherwise("/")

    reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.InterfaceApp'))
    ReportsRouting(reportStates)
    for w in reportStates.whens
      $urlRouterProvider.when(w[0], w[1])
    for r in reportStates.routes
      r.applyToStateProvider($stateProvider)
  ])

  return InterfaceApp