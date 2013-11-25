define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Agents_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = []

		init: ->
			@form = {}
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				agent: "/agents/#{@$stateParams.id}",
				teams: "/agent_teams",
				groups: "/agentgroups"
			}).then( (result) =>
				@agent = result.data.agent.agent
				@teams = result.data.teams.agent_teams
				@groups = result.data.groups.agentgroups
			)
			return

	Admin_Agents_Ctrl_Edit.EXPORT_CTRL()