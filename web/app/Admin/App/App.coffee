	define [
	'angular',
	'Admin/App/AdminModule',

	'Admin/App/SetupDataServices',
	'Admin/App/SetupDirectives',
	'DeskPRO/App/SetupLogging',
	'DeskPRO/App/SetupNetwork',
	'Admin/App/SetupRouting',
	'DeskPRO/App/SetupServices',
	'Admin/App/SetupServices',
	'Admin/App/SetupTemplates',
], (
	angular,
	AdminModule,

	SetupDataServices,
	SetupDirectives,
	SetupLogging,
	SetupNetwork,
	SetupRouting,
	SetupServices,
	AdminSetupServices,
	SetupTemplates
) ->

	SetupServices(AdminModule)
	AdminSetupServices(AdminModule)
	SetupLogging(AdminModule)
	SetupDataServices(AdminModule)

	AdminModule.factory('dpHttpSessionInterceptor', ['$q', ($q) ->
		return {
			responseError: (rejection) ->
				if rejection.status? and rejection.data?.error? and rejection.status == 403 and rejection.data.error == "session_expired"
					window.location = window.DP_BASE_URL + 'agent/login?timeout=1&return=' + encodeURIComponent(window.DP_BASE_URL + 'admin/' + window.location.hash);
				else
					return $q.reject(rejection)
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

	if window.parent?.DP_FRAME_OVERLAYS?.admin
		window.parent.DP_FRAME_OVERLAYS.admin.callLoaded()

		AdminModule.run(['$rootScope', ($rootScope) ->

			if not window.DP_REDIRECT_TO_LICENSE
				$rootScope.$on('$stateChangeSuccess', ->
					window.parent.DP_FRAME_OVERLAYS.admin.setHash(window.location.hash)
				)
		])

	return AdminModule
