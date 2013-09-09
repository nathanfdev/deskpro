define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_SettingsNav extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_SettingsNav'
		@MODULE_ID = 'Admin_App'
		@DEPS      = ['$scope']

		init: ->

	Admin_Main_Ctrl_SettingsNav.EXPORT_CTRL()