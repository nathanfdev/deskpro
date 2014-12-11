define [
  'angular',
  'DeskPRO/Main/Service/DpApi',

  'Reports/App/Controller/Main',
  'Reports/App/Controller/Dashboard',


  'Reports/App/Service/Dashboard'
  'Reports/App/Service/DashboardWidget'

], (
  angular,
  DeskPRO_Main_Service_DpApi,
  Reports_App_Controller_Main,
  Reports_App_Controller_Dashboard,


  Reports_App_Service_Dashboard
  Reports_App_Service_DashboardWidget

) ->
  ReportsApp = angular.module('DeskPRO.ReportsApp', ['DeskPRO.InterfaceApp'])

  ReportsApp.factory('dpHttpInterceptor', ['$q', ($q) ->
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

  ReportsApp.config(['$httpProvider', ($httpProvider) ->
                           $httpProvider.interceptors.push('dpHttpInterceptor');
  ])

  ReportsApp.controller('Reports.App.Main', Reports_App_Controller_Main)
  ReportsApp.controller('Reports.App.Dashboard', Reports_App_Controller_Dashboard)
#  ReportsApp.controller('Reports.App.ModalInstance', Reports_App_Controller_ModalInstance)

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

  return ReportsApp
