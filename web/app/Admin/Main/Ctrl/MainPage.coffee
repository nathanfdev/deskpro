define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_MainPage extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_MainPage'
		@DEPS      = ['$rootScope', 'AppState']

		init: ->
			return

	Admin_Main_Ctrl_MainPage.EXPORT_CTRL()