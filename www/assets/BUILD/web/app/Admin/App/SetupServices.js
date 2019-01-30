define [
  'Admin/Main/Service/SessionPing',
  'Admin/Main/Service/DpDate',
  'Admin/Main/Service/LangSyncApi',
  'Admin/License/Service/DpLicense',
  'Admin/Cloud/App/CloudService',
  'angular'
], (
  Admin_Main_Service_SessionPing,
  Admin_Main_Service_DpDate,
  Admin_Main_Service_LangSyncApi,
  Admin_License_Service_DpLicense,
  Admin_Cloud_App_CloudService,
  angular
) ->
  return (Module) ->
    Module.service('SessionPing', ['Api', (Api) ->
      return new Admin_Main_Service_SessionPing(Api)
    ])

    Module.service('DpLicense', ['Api', '$modal', '$http', '$q', (Api, $modal, $http, $q) ->
      return new Admin_License_Service_DpLicense(Api, $modal, $http, $q)
    ])

    Module.service('DpDateService', Admin_Main_Service_DpDate)

    Module.service('Cloud', [ ->
      return new Admin_Cloud_App_CloudService()
    ])

    Module.service('LangSyncApi', ['$http', 'Growl', ($http, Growl) ->
      return new Admin_Main_Service_LangSyncApi(
        $http,
        window.DP_LANGUAGE_SYNC_API,
        Growl
      )
    ])

    Module.run(['SessionPing', (SessionPing) ->
      # start pinging after 20 seconds
      window.setTimeout(->
        SessionPing.startInterval()
      , 20000)
    ])

    Module.run(['$rootScope', 'dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketFilter', ($rootScope, dpObTypesDefTicketCriteria, dpObTypesDefTicketActions, dpObTypesDefTicketFilter) ->
      getAppStateName = (name) ->
        parts = name.split('.')
        while parts.length > 2
          parts.pop()
        return parts.join('.')

      $rootScope.$on('$stateChangeSuccess', (event, toState, toParams, fromState, fromParams) ->
        if not fromState or not toState
          return

        # When navigating to a new section, clear cached trigger options
        last = getAppStateName(fromState.name)
        now  = getAppStateName(toState.name)
        if last != now
          dpObTypesDefTicketCriteria.resetData()
          dpObTypesDefTicketActions.resetData()
          dpObTypesDefTicketFilter.resetData()
      )
    ])

    Module.filter('orderObjectBy', ->
      (items, field, reverse) ->
        filtered = []
        angular.forEach(items, (item) ->
          filtered.push item
        )

        filtered.sort (a, b) ->
          if a[field] > b[field] then 1 else -1

        reverse && filtered.reverse()
        return filtered
    )
