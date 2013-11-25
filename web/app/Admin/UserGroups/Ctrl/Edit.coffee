define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_UserGroups_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserGroups_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []

		init: ->
			return

	Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL()