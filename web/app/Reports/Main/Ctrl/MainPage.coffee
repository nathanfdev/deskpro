define [
	'Reports/Main/Ctrl/Base'
], (
	ReportsBaseCtrl
) ->
	class Reports_Main_Ctrl_MainPage extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Main_Ctrl_MainPage'
		@DEPS      = ['$rootScope', 'AppState']

		init: ->
			return

	Reports_Main_Ctrl_MainPage.EXPORT_CTRL()