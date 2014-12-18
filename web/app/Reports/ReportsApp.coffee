define [
  'angular',
  'DeskPRO/Main/Service/DpApi',

  'Reports/App/Controller/Main',
  'Reports/App/Controller/Dashboard',
  'Reports/App/Controller/ModalDashboard',
  'Reports/App/Controller/ModalReport',


  'Reports/App/Service/Dashboard'
  'Reports/App/Service/DashboardWidget'
  'Reports/App/Service/DashboardPermissions'

  'Reports/App/Directive/DashboardAmcharts',
  'Reports/App/Directive/DashboardStat',
  'Reports/App/Directive/DashboardTable',

], (
  angular,
  DeskPRO_Main_Service_DpApi,
  Reports_App_Controller_Main,
  Reports_App_Controller_Dashboard,
  Reports_App_Controller_ModalDashboard,
  Reports_App_Controller_ModalReport,

  Reports_App_Service_Dashboard,
  Reports_App_Service_DashboardWidget,
  Reports_App_Service_DashboardPermissions,

  Reports_App_Directive_DashboardAmcharts,
  Reports_App_Directive_DashboardStat,
  Reports_App_Directive_DashboardTable,

) ->
  ReportsApp = angular.module('DeskPRO.ReportsApp', ['DeskPRO.InterfaceApp'])


  ###
  # Controllers section
  ###
  ReportsApp.controller('Reports.App.Main', Reports_App_Controller_Main)
  ReportsApp.controller('Reports.App.Dashboard', Reports_App_Controller_Dashboard)
  ReportsApp.controller('Reports.App.ModalDashboard', Reports_App_Controller_ModalDashboard)
  ReportsApp.controller('Reports.App.ModalReport', Reports_App_Controller_ModalReport)

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

  ###
  # Directives section
  ###
  ReportsApp.directive('DashboardAmchartsDirective', Reports_App_Directive_DashboardAmcharts)
  ReportsApp.directive('DashboardStatDirective', Reports_App_Directive_DashboardStat)
  ReportsApp.directive('DashboardTableDirective', Reports_App_Directive_DashboardTable)

  return ReportsApp
