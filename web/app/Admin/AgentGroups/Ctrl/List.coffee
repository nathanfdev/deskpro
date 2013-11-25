define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_AgentGroups_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentGroups_Ctrl_List'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			promise = @Api.sendGet('/agentgroups').then( (result) =>
				@groups = result.data.agentgroups
			)
			return promise

	Admin_AgentGroups_Ctrl_List.EXPORT_CTRL()