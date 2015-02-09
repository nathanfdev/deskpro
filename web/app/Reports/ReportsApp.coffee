define [
  'angular',
  'DeskPRO/Main/Service/DpApi',

  # Controllers
  'Reports/Dashboards/Ctrl/DashboardReport',
  'Reports/Dashboards/Ctrl/DashboardView',
  'Reports/Dashboards/ModalCtrl/EditDashboard',
  'Reports/Dashboards/ModalCtrl/ChooseWidget',
  'Reports/Dashboards/ModalCtrl/AddWidget',
  'Reports/Dashboards/ModalCtrl/EditWidget',

  'Reports/Stats/Ctrl/StatsMain',
  'Reports/Stats/Ctrl/StatsHome',
  'Reports/Stats/Ctrl/WidgetView',

  'Reports/Legacy/AgentActivity/Ctrl/AgentActivity',
  'Reports/Legacy/AgentHours/Ctrl/AgentHours',
  'Reports/Legacy/Builder/Ctrl/Edit',
  'Reports/Legacy/Builder/Ctrl/List',
  'Reports/Legacy/TicketSatisfaction/Ctrl/TicketSatisfaction',

  # STANDARD SERVICES
  'Reports/App/Service/Dashboard',
  'Reports/App/Service/DashboardWidget',
  'Reports/App/Service/DashboardPermissions',
  'Reports/App/Service/DashboardsInfo',

  'DeskPRO/Main/Service/AppState',
  'DeskPRO/Main/Service/Growl',
  'DeskPRO/Logger/Logging/InterfaceTimer',

  'Admin/Main/DataService/EntityManager',
  'Reports/Legacy/Service/DataServiceManager',

  #STANDARD DIRECTIVES
  'Reports/App/Directive/DashboardAmcharts',
  'Reports/App/Directive/DashboardStat',
  'Reports/App/Directive/DashboardTable',

  'Reports/Legacy/Directive/DpReportBuilderSelectBox',
  'Reports/Legacy/Directive/DpReportBillingSelectBox',
  'Reports/Legacy/Directive/DpReportBuilderTitle',

  # DP DIRECTIVES
  'Reports/App/Directive/DpReportWidgetSelectBox',
  'DeskPRO/Directive/DpDropdown',

  #jquery
  'jquery',
  'jqueryUi',

  #angular modules
  'ngTable',
  'angularGridster',
  'angularUiSortable',
  'amcharts',
  'amcharts.pie',
  'amcharts.serial',
  'underscore'
], (
  angular,
  DeskPRO_Main_Service_DpApi,

  # Controllers
  Reports_Dashboards_Ctrl_DashboardReport,
  Reports_Dashboards_Ctrl_DashboardView,
  Reports_Dashboards_ModalCtrl_EditDashboard,
  Reports_Dashboards_ModalCtrl_ChooseWidget,
  Reports_Dashboards_ModalCtrl_AddWidget,
  Reports_Dashboards_ModalCtrl_EditWidget,

  Reports_Stats_Ctrl_StatsMain,
  Reports_Stats_Ctrl_StatsHome,
  Reports_Stats_Ctrl_WidgetView,

  Reports_AgentActivity_Ctrl_AgentActivity,
  Reports_AgentHours_Ctrl_AgentHours,
  Reports_Builder_Ctrl_Edit,
  Reports_Builder_Ctrl_List,
  Reports_TicketSatisfaction_Ctrl_TicketSatisfaction,

  Reports_App_Service_Dashboard,
  Reports_App_Service_DashboardWidget,
  Reports_App_Service_DashboardPermissions,
  Reports_App_Service_DashboardsInfo,

  DeskPRO_Main_Service_AppState,
  DeskPRO_Main_Service_Growl,
  DeskPRO_Logging_InterfaceTimer,

  Admin_Main_DataService_EntityManager,
  Reports_App_Service_DataServiceManager,

  Reports_App_Directive_DashboardAmcharts,
  Reports_App_Directive_DashboardStat,
  Reports_App_Directive_DashboardTable,

  Reports_Directive_DpReportBuilderSelectBox,
  Reports_Directive_DpReportBillingSelectBox,
  Reports_Directive_DpReportBuilderTitle,

  # DP DIRECTIVES
  Reports_App_Directive_DpReportWidgetSelectBox,
  Reports_App_Directive_DpDropdown,

) ->
  ReportsApp = angular.module('DeskPRO.ReportsApp', ['DeskPRO.InterfaceApp', 'gridster', 'ui.sortable'])

  ###
  # Controllers section
  ###
  ReportsApp.controller('Reports.App.DashboardReport',             Reports_Dashboards_Ctrl_DashboardReport)
  ReportsApp.controller('Reports.App.DashboardView',               Reports_Dashboards_Ctrl_DashboardView)
  ReportsApp.controller('Reports.Dashboards.Modals.EditDashboard', Reports_Dashboards_ModalCtrl_EditDashboard)
  ReportsApp.controller('Reports.Dashboards.Modals.ChooseWidget',  Reports_Dashboards_ModalCtrl_ChooseWidget)
  ReportsApp.controller('Reports.Dashboards.Modals.AddWidget',     Reports_Dashboards_ModalCtrl_AddWidget)
  ReportsApp.controller('Reports.Dashboards.Modals.EditWidget',    Reports_Dashboards_ModalCtrl_EditWidget)

  ReportsApp.controller('Reports.Stats.StatsMain',                 Reports_Stats_Ctrl_StatsMain)
  ReportsApp.controller('Reports.Stats.StatsHome',                 Reports_Stats_Ctrl_StatsHome)
  ReportsApp.controller('Reports.Stats.WidgetView',                Reports_Stats_Ctrl_WidgetView)

  # legacy controllers
  for x in window.DP_CTRL_REG
    deps = x[1]
    ctrl = deps.pop()
    ctrl.$inject = deps

  ReportsApp.controller('Reports.AgentActivity.AgentActivity',           Reports_AgentActivity_Ctrl_AgentActivity);
  ReportsApp.controller('Reports.AgentHours.AgentHours',                 Reports_AgentHours_Ctrl_AgentHours,);
  ReportsApp.controller('Reports.Builder.Edit',                          Reports_Builder_Ctrl_Edit);
  ReportsApp.controller('Reports.Builder.List',                          Reports_Builder_Ctrl_List);
  ReportsApp.controller('Reports.TicketSatisfaction.TicketSatisfaction', Reports_TicketSatisfaction_Ctrl_TicketSatisfaction);

  ###
  # Service section
  ###
  ReportsApp.service('Api', ['$http', ($http) ->
    return new DeskPRO_Main_Service_DpApi(
      $http,
      window.DP_BASE_API_URL,
      window.DP_API_TOKEN
    )
  ])

  # DpApi (legacy name) alias for Api
  ReportsApp.service('DpApi', ['Api', (Api) ->
    return Api
  ])

  ReportsApp.service('AppState', ['$rootScope', '$state', ($rootScope, $state) ->
    return new DeskPRO_Main_Service_AppState($rootScope, $state)
  ])

  ReportsApp.service('em', [ ->
    return new Admin_Main_DataService_EntityManager()
  ])

  ReportsApp.factory('DataService', [ '$injector', ($injector) ->
    return new Reports_App_Service_DataServiceManager($injector)
  ])

  ReportsApp.service('Growl', [ ->
    return new DeskPRO_Main_Service_Growl()
  ])

  ReportsApp.factory('dpInterfaceTimer', [ '$log', ($log) ->
    return new DeskPRO_Logging_InterfaceTimer($log)
  ])

  ReportsApp.filter('escape_url', [ ->
    return (text) ->
      return encodeURIComponent(text)
  ])

  ReportsApp.filter('murmurhash', [ ->
    return (text) ->
      return Strings.murmurhash3(text)
  ])

  ReportsApp.filter('filesize_display', [ ->
    return (bytes, precision = 2) ->
      if not bytes
        bytes = 0
      if not Util.isNumber(bytes)
        if Util.isString(bytes)
          bytes = parseFloat(bytes)
          if not bytes
            bytes = 0
        else
          bytes = 0

      if bytes == 0
        return '0 B'

      symbols = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

      exp = Math.floor(Math.log(bytes) / Math.log(1024))
      result = bytes / Math.pow(1024, Math.floor(exp))
      result = result.toFixed(precision)

      if symbols[exp] then result += ' ' + symbols[exp]

      return result
  ])

  ReportsApp.filter('fulltime', ['$filter', ($filter) ->
    return (timestamp) ->
      return $filter('date')(timestamp, 'EEEE, MMMM d, y h:mm a')
  ])

  ReportsApp.service('DashboardService', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_Dashboard(Api, $q)
  ])
  ReportsApp.service('DashboardWidgetService', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_DashboardWidget(Api, $q)
  ])
  ReportsApp.service('DashboardPermissionsService', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_DashboardPermissions(Api, $q)
  ])
  ReportsApp.service('DashboardsInfo', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_DashboardsInfo(Api, $q)
  ])

  ###
  # Directives section
  ###
  ReportsApp.directive('dashboardAmcharts',         Reports_App_Directive_DashboardAmcharts)
  ReportsApp.directive('dpDropdown',                Reports_App_Directive_DpDropdown)
  ReportsApp.directive('dpReportWidgetSelectBox',   Reports_App_Directive_DpReportWidgetSelectBox)
  ReportsApp.directive('dashboardTable',            Reports_App_Directive_DashboardTable)

  ReportsApp.directive('dpReportBuilderSelectBox',       Reports_Directive_DpReportBuilderSelectBox)
  ReportsApp.directive('dpReportBillingSelectBox',       Reports_Directive_DpReportBillingSelectBox)
  ReportsApp.directive('dpReportBuilderTitle',           Reports_Directive_DpReportBuilderTitle)

  return ReportsApp
