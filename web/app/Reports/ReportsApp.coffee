define [
  'angular',
  'Reports/App/Controller/Main',


  'Reports/App/Service/Dashboard'

], (
  angular,
  Reports_App_Controller_Main,


  Reports_App_Service_Dashboard

) ->
  ReportsApp = angular.module('DeskPRO.ReportsApp', ['DeskPRO.InterfaceApp'])

  ReportsApp.controller('Reports.App.Main', Reports_App_Controller_Main)
#  ReportsApp.controller('Reports.App.Dashboard', Reports_App_Controller_Dashboard)
#  ReportsApp.controller('Reports.App.ModalInstance', Reports_App_Controller_ModalInstance)


  ReportsApp.service('DashboardService', ['$http', '$q', ($http, $q) ->
    return new Reports_App_Service_Dashboard($http, $q)
  ])

  return ReportsApp
