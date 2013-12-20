define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Templates_Ctrl_EmailGroupList extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Templates_Ctrl_EmailGroupList'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

	Admin_Templates_Ctrl_EmailGroupList.EXPORT_CTRL()