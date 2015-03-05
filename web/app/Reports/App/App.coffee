define [
  'angular',
  'Reports/App/ReportsModule',

  'Reports/App/SetupDataServices',
  'Reports/App/SetupDirectives',
  'DeskPRO/App/SetupLogging',
  'DeskPRO/App/SetupNetwork',
  'Reports/App/SetupRouting',
  'DeskPRO/App/SetupServices',
  'Reports/App/SetupTemplates',
  'Reports/Main/Service/SessionPing',
], (
  angular,
  ReportsModule,

  SetupDataServices,
  SetupDirectives,
  SetupLogging,
  SetupNetwork,
  SetupRouting,
  SetupServices,
  SetupTemplates,

  Reports_Main_Service_SessionPing
) ->

  SetupServices(ReportsModule)
  SetupLogging(ReportsModule)
  SetupDataServices(ReportsModule)
  SetupNetwork(ReportsModule)
  SetupDirectives(ReportsModule)
  SetupRouting(ReportsModule)
  SetupTemplates(ReportsModule)

  ReportsModule.factory('dpHttpSessionInterceptor', ['$q', ($q) ->
    return {
    responseError: (rejection) ->
      if rejection.status? and rejection.data?.error? and rejection.status == 403 and rejection.data.error == "session_expired"
        window.location = window.DP_BASE_URL + 'agent/login?timeout=1&return=' + encodeURIComponent(window.DP_BASE_URL + 'reports/' + window.location.hash);
      else
        return $q.reject(rejection)
    }
  ])
  ReportsModule.config(['$httpProvider', ($httpProvider) ->
    $httpProvider.interceptors.push('dpHttpSessionInterceptor');
  ])

  ReportsModule.service('SessionPing', ['Api', (Api) ->
    return new Reports_Main_Service_SessionPing(Api)
  ])
  ReportsModule.run(['SessionPing', (SessionPing) ->
    window.setTimeout(->
      SessionPing.startInterval()
    , 20000)
  ])

  if window.parent?.DP_FRAME_OVERLAYS?.reports
    window.parent.DP_FRAME_OVERLAYS.reports.callLoaded()

    ReportsModule.run(['$rootScope', ($rootScope) ->
      $rootScope.$on('$stateChangeSuccess', ->
        window.parent.DP_FRAME_OVERLAYS.reports.setHash(window.location.hash)
      )
    ])

  return ReportsModule