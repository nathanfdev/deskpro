define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_AgentGroups_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentGroups_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			return

	Admin_AgentGroups_Ctrl_Edit.EXPORT_CTRL()