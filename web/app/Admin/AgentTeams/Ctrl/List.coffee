define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_AgentTeams_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentTeams_Ctrl_List'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			promise = @Api.sendGet('/agent_teams').then( (result) =>
				@teams = result.data.agent_teams
			)
			return promise

		addTeam: (team) ->
			@teams.push(team)

		removeTeamById: (teamId) ->
			teamId = parseInt(teamId)
			@teams = @teams.filter((x) -> x.id != teamId)

		renameTeamById: (teamId, name) ->
			@teams.filter((x) -> x.id == teamId).map((x) -> x.name = name)

	Admin_AgentTeams_Ctrl_List.EXPORT_CTRL()