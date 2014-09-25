define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Agents_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_List'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->
			@service =
				agents: @DataService.get 'Agents'
			return

		initialLoad: ->
			@service.agents.all().then (agents) =>
				@agents = agents

			promise = @Api.sendDataGet({
				deleted_agents: '/agents/deleted'
			}).then( (result) =>
				@deletedCount = result.data.deleted_agents.agents.length
			)
			return promise

		removeAgentFromList: (id) ->
			@deletedCount++

	Admin_Agents_Ctrl_List.EXPORT_CTRL()