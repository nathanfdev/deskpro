define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserGroups_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserGroups_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS = []

		init: ->
			return

	Admin_UserGroups_Ctrl_List.EXPORT_CTRL()