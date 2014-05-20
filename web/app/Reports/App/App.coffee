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
], (
	angular,
	ReportsModule,

	SetupDataServices,
	SetupDirectives,
	SetupLogging,
	SetupNetwork,
	SetupRouting,
	SetupServices,
	SetupTemplates
) ->

	SetupServices(ReportsModule)
	SetupLogging(ReportsModule)
	SetupDataServices(ReportsModule)
	SetupNetwork(ReportsModule)
	SetupDirectives(ReportsModule)
	SetupRouting(ReportsModule)
	SetupTemplates(ReportsModule)

	if window.parent?.DP_FRAME_OVERLAYS?.reports
		window.parent.DP_FRAME_OVERLAYS.reports.callLoaded()

		ReportsModule.run(['$rootScope', ($rootScope) ->
			$rootScope.$on('$stateChangeSuccess', ->
				window.parent.DP_FRAME_OVERLAYS.reports.setHash(window.location.hash)
			)
		])

	return ReportsModule