define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_AgentTeams_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentTeams_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			return

	Admin_AgentTeams_Ctrl_Edit.EXPORT_CTRL()