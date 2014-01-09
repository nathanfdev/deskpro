define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_AgentTeams_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentTeams_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = []

		init: ->
			@teamId = parseInt(@$stateParams.id)
			return

		initialLoad: ->
			if @teamId
				promise = @Api.sendDataGet({
					team: "/agent_teams/#{@teamId}",
					agents: "/agents"
				})
			else
				promise = @Api.sendDataGet({
					agents: "/agents"
				})

			promise.then( (res) =>
				@agents = res.data.agents.agents
				@team   = res.data.team.team
			)
			return promise

		saveForm: ->
			postData = {
				name: @team.name,
				person_ids: []
			}

			for a in @agents
				if a.value
					postData.person_ids.push(a.id)

			if @teamId
				p = @sendFormSaveApiCall('POST', "/agent_teams/#{@teamId}", postData)
			else
				p = @sendFormSaveApiCall('PUT', "/agent_teams", postData)

			p.then(=>

			)
			return

	Admin_AgentTeams_Ctrl_Edit.EXPORT_CTRL()