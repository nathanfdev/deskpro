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
	SetupNetwork(AdminModule)
	SetupDirectives(AdminModule)
	SetupRouting(AdminModule)
	SetupTemplates(AdminModule)

	if window.parent?.DP_FRAME_OVERLAYS?.admin
		window.parent.DP_FRAME_OVERLAYS.admin.callLoaded()

		AdminModule.run(['$rootScope', ($rootScope) ->
			$rootScope.$on('$stateChangeSuccess', ->
				window.parent.DP_FRAME_OVERLAYS.admin.setHash(window.location.hash)
			)
		])

	return AdminModule