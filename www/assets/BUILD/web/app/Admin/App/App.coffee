define [
  'angular',
  'moment',
  'Admin/App/AdminModule',

  'Admin/App/SetupDataServices',
  'Admin/App/SetupDirectives',
  'DeskPRO/App/SetupLogging',
  'DeskPRO/App/SetupNetwork',
  'Admin/App/SetupRouting',
  'DeskPRO/App/SetupServices',
  'Admin/App/SetupServices',
  'Admin/App/SetupTemplates',
  'DeskPRO/Util/Util'
], (
  angular,
  moment,
  AdminModule,

  SetupDataServices,
  SetupDirectives,
  SetupLogging,
  SetupNetwork,
  SetupRouting,
  SetupServices,
  AdminSetupServices,
  SetupTemplates,

  Util
) ->

  SetupServices(AdminModule)
  AdminSetupServices(AdminModule)
  SetupLogging(AdminModule)
  SetupDataServices(AdminModule)

  AdminModule.factory('dpHttpSessionInterceptor', ['$q', ($q) ->
    return {
      responseError: (rejection) ->
        if rejection.status? and rejection.status == 403
          window.location = window.DP_BASE_URL + 'agent/login?timeout=1&return=' + encodeURIComponent(window.DP_BASE_URL + 'admin/' + window.location.hash);
        return $q.reject(rejection)
      response: (response) ->
        if response.config.method == 'POST' && response.status >= 200 && response.status < 400 && window.parent
          if !response.config.url.match(/^\/__serverinfo/)
            window.parent.DP_NEED_RELOAD = true
        return response

    }
  ])
  AdminModule.config(['$httpProvider', ($httpProvider) ->
    $httpProvider.interceptors.push('dpHttpSessionInterceptor');
  ])
  AdminModule.constant('angularMomentConfig', {
    timezone: window.DP_PERSON_TZ
  })

  SetupNetwork(AdminModule)
  SetupDirectives(AdminModule)
  SetupRouting(AdminModule)
  SetupTemplates(AdminModule)

  if window.DP_REDIRECT_TO_LICENSE
    console.log("Redirect to license")
    window.location.hash = '/license'

  try
    if window.parent?.DP_FRAME_OVERLAYS?.admin
      AdminModule.run(['$rootScope', ($rootScope) ->

        if not window.DP_REDIRECT_TO_LICENSE
          $rootScope.$on('$stateChangeSuccess', ->
            if window.parent.DP_FRAME_OVERLAYS.admin.opened
              window.parent.DP_FRAME_OVERLAYS.admin.setHash(window.location.hash)
          )
      ])
  catch e
    console.log e

  return AdminModule
