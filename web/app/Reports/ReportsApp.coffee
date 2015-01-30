define [
  'angular',
  'DeskPRO/Main/Service/DpApi',

  # Controllers
  'Reports/App/Controller/DashboardReport',
  'Reports/App/Controller/DashboardView',
  'Reports/App/Controller/ModalDashboard',
  'Reports/App/Controller/ModalReport',
  'Reports/App/Controller/ModalWidgetType',
  'Reports/App/Controller/ModalWidgetAdd',
  'Reports/App/Controller/ModalWidgetEdit',

  # STANDARD SERVICES
  'Reports/App/Service/Dashboard',
  'Reports/App/Service/DashboardWidget',
  'Reports/App/Service/DashboardPermissions',
  'Reports/App/Service/DashboardsInfo',

  # SERVICES FOR HARDCODED DATA
  'Reports/App/Service/ReportsOverview',
  'Reports/App/Service/AgentActivity',
  'Reports/App/Service/AgentHours',
  'Reports/App/Service/TicketSatisfaction',

  #STANDARD DIRECTIVES
  'Reports/App/Directive/DashboardAmcharts',
  'Reports/App/Directive/DashboardStat',
  'Reports/App/Directive/DashboardTable',

  # DIRECTIVES FOR HARDCODED DATA
  'Reports/App/Directive/ReportsOverview',
  'Reports/App/Directive/AgentPerformance',
  'Reports/App/Directive/TicketSatisfaction',

  # DP DIRECTIVES
  'Reports/App/Directive/DpReportWidgetSelectBox',
  'DeskPRO/Directive/DpDropdown',

  #angular modules
  'ngTable',
  'angularGridster',
  'amcharts',
  'amcharts.pie',
  'amcharts.serial',
], (
  angular,
  DeskPRO_Main_Service_DpApi,

  # Controllers
  Reports_App_Controller_DashboardReport,
  Reports_App_Controller_DashboardView,
  Reports_App_Controller_ModalDashboard,
  Reports_App_Controller_ModalReport,
  Reports_App_Controller_ModalWidgetType,
  Reports_App_Controller_ModalWidgetAdd,
  Reports_App_Controller_ModalWidgetEdit,

  Reports_App_Service_Dashboard,
  Reports_App_Service_DashboardWidget,
  Reports_App_Service_DashboardPermissions,
  Reports_App_Service_DashboardsInfo,

  Reports_App_Service_ReportsOverview,
  Reports_App_Service_AgentActivity,
  Reports_App_Service_AgentHours,
  Reports_App_Service_TicketSatisfaction,


  Reports_App_Directive_DashboardAmcharts,
  Reports_App_Directive_DashboardStat,
  Reports_App_Directive_DashboardTable,

  # DIRECTIVES FOR HARDCODED DATA
  Reports_App_Directive_ReportsOverview,
  Reports_App_Directive_AgentPerformance,
  Reports_App_Directive_TicketSatisfaction,
  # DP DIRECTIVES
  Reports_App_Directive_DpReportWidgetSelectBox,
  Reports_App_Directive_DpDropdown,

) ->
  ReportsApp = angular.module('DeskPRO.ReportsApp', ['DeskPRO.InterfaceApp', 'gridster'])

  ###
  # Controllers section
  ###
  ReportsApp.controller('Reports.App.DashboardReport',      Reports_App_Controller_DashboardReport)
  ReportsApp.controller('Reports.App.DashboardView',        Reports_App_Controller_DashboardView)

  ReportsApp.controller('Reports.App.ModalDashboard',       Reports_App_Controller_ModalDashboard)
  ReportsApp.controller('Reports.App.ModalReport',          Reports_App_Controller_ModalReport)
  ReportsApp.controller('Reports.App.ModalWidgetType',      Reports_App_Controller_ModalWidgetType)
  ReportsApp.controller('Reports.App.ModalWidgetAdd',       Reports_App_Controller_ModalWidgetAdd)
  ReportsApp.controller('Reports.App.ModalWidgetEdit',      Reports_App_Controller_ModalWidgetEdit)

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
  ReportsApp.service('ReportsOverviewService', ['Api', '$q', (Api, $q) ->
    return new Reports_App_Service_ReportsOverview(Api, $q)
  ])
  ReportsApp.service('AgentActivityService', ['Api', '$sce', (Api, $sce) ->
    return new Reports_App_Service_AgentActivity(Api, $sce)
  ])
  ReportsApp.service('AgentHoursService', ['Api', '$sce', '$q', (Api, $sce, $q) ->
    return new Reports_App_Service_AgentHours(Api, $sce, $q)
  ])
  ReportsApp.service('TicketSatisfactionService', ['Api', '$sce', '$q', '$timeout', (Api, $sce, $q, $timeout) ->
    return new Reports_App_Service_TicketSatisfaction(Api, $sce, $q, $timeout)
  ])

  ###
  # Directives section
  ###
  ReportsApp.directive('dashboardAmcharts',         Reports_App_Directive_DashboardAmcharts)
  ReportsApp.directive('reportsOverview',           Reports_App_Directive_ReportsOverview)
  ReportsApp.directive('agentPerformance',          Reports_App_Directive_AgentPerformance)
  ReportsApp.directive('ticketSatisfaction',        Reports_App_Directive_TicketSatisfaction)
  ReportsApp.directive('dpDropdown',                Reports_App_Directive_DpDropdown)
  ReportsApp.directive('dpReportWidgetSelectBox',   Reports_App_Directive_DpReportWidgetSelectBox)
  ReportsApp.directive('dashboardTable',            Reports_App_Directive_DashboardTable)

  return ReportsApp
