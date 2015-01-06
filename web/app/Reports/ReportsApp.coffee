define [
  'angular',
  'DeskPRO/Main/Service/DpApi',

  'Reports/App/Controller/Main',
  'Reports/App/Controller/Dashboard',

  # Modal controllers
  'Reports/App/Controller/ModalDashboard',
  'Reports/App/Controller/ModalWidgetType',
  'Reports/App/Controller/ModalWidgetEdit',

  # STANDARD SERVICES
  'Reports/App/Service/Dashboard'
  'Reports/App/Service/DashboardWidget'
  'Reports/App/Service/DashboardPermissions'

  # SERVICES FOR HARDCODED DATA
  'Reports/App/Service/ReportsOverview'
  'Reports/App/Service/AgentActivity'
  'Reports/App/Service/AgentHours'
  'Reports/App/Service/TicketSatisfaction'


  #STANDARD DIRECTIVES
  'Reports/App/Directive/DashboardAmcharts',
  'Reports/App/Directive/DashboardStat',
  'Reports/App/Directive/DashboardTable',

  # DIRECTIVES FOR HARDCODED DATA
  'Reports/App/Directive/ReportsOverview',
  'Reports/App/Directive/AgentPerformance',
  'Reports/App/Directive/TicketSatisfaction',

  # DP DIRECTIVES
  'DeskPRO/Directive/DpShowSpinning',
  'DeskPRO/Directive/DpHideSpinning',

  #angular modules
  'ngTable',
  'angularGridster',
  'amcharts',
  'amcharts.pie',
  'amcharts.serial',

], (
  angular,
  DeskPRO_Main_Service_DpApi,
  Reports_App_Controller_Main,
  Reports_App_Controller_Dashboard,

  # Modal controllers
  Reports_App_Controller_ModalDashboard,
  Reports_App_Controller_ModalWidgetType,
  Reports_App_Controller_ModalWidgetEdit,

  Reports_App_Service_Dashboard,
  Reports_App_Service_DashboardWidget,
  Reports_App_Service_DashboardPermissions,

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
  Reports_App_Directive_DpShowSpinning,
  Reports_App_Directive_DpHideSpinning,

) ->
  ReportsApp = angular.module('DeskPRO.ReportsApp', ['DeskPRO.InterfaceApp', 'gridster'])


  ###
  # Controllers section
  ###
  ReportsApp.controller('Reports.App.Main', Reports_App_Controller_Main)
  ReportsApp.controller('Reports.App.Dashboard', Reports_App_Controller_Dashboard)

  # Modal controllers
  ReportsApp.controller('Reports.App.ModalDashboard', Reports_App_Controller_ModalDashboard)
  ReportsApp.controller('Reports.App.ModalWidgetType', Reports_App_Controller_ModalWidgetType)
  ReportsApp.controller('Reports.App.ModalWidgetEdit', Reports_App_Controller_ModalWidgetEdit)

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
  ReportsApp.directive('dashboardAmcharts', Reports_App_Directive_DashboardAmcharts)
  ReportsApp.directive('reportsOverview', Reports_App_Directive_ReportsOverview)
  ReportsApp.directive('agentPerformance', Reports_App_Directive_AgentPerformance)
  ReportsApp.directive('ticketSatisfaction', Reports_App_Directive_TicketSatisfaction)
  ReportsApp.directive('dpShowSpinning', Reports_App_Directive_DpShowSpinning)
  ReportsApp.directive('dpHideSpinning', Reports_App_Directive_DpHideSpinning)
#  ReportsApp.directive('dashboardStat', Reports_App_Directive_DashboardStat)
#  ReportsApp.directive('dashboardTable', Reports_App_Directive_DashboardTable)

  return ReportsApp
