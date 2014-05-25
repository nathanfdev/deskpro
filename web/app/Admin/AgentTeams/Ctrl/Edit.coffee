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

				if @teamId
					@team = res.data.team.team
				else
					@team = {members: []}

				# value=true on agents that are members
				memberIds = @team.members.map((x) -> x.id)
				@agents.map((x) -> if x.id in memberIds then x.value = true)
			)
			return promise

		saveForm: ->
			postData = {
				team: {
					name: @team.name
					person_ids: []
				}
			}

			for a in @agents
				if a.value
					postData.team.person_ids.push(a.id)

			if @teamId
				p = @sendFormSaveApiCall('POST', "/agent_teams/#{@teamId}", postData)
			else
				p = @sendFormSaveApiCall('PUT', "/agent_teams", postData)

			p.then( (res) =>
				@Growl.success(@getRegisteredMessage('saved_team'))

				if @teamId
					@getTeamListCtrl().renameTeamById(@teamId, @team.name)
				else
					@teamId = res.data.team_id
					@getTeamListCtrl().addTeam({ id: @teamId, name: @team.name})
					@$state.go('agents.teams.edit', {id: @teamId})
			)
			return

		###
    	# Shows the copy settings modal
    	###
		showDelete: ->
			deleteTeam = =>
				p = @Api.sendDelete("/agent_teams/#{@teamId}")
				p.then(=>
					@getTeamListCtrl().removeTeamById(@teamId)
					@$state.go('agents.agents')
				)
				return p

			inst = @$modal.open({
				templateUrl: @getTemplatePath('AgentTeams/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doDelete = (options) ->
						$scope.is_loading = true
						deleteTeam().then(-> $modalInstance.dismiss())
				]
			});


		###
    	# Gets a reference to the parent list view which we need to update with the new details
		###
		getTeamListCtrl: ->
			if @$scope.$parent?.ListCtrl?
				return @$scope.$parent.ListCtrl
			else
				# mock since list isnt there yet
				return {
					addTeam:        -> return
					removeTeamById: -> return
					renameTeamById: -> return
				}

	Admin_AgentTeams_Ctrl_Edit.EXPORT_CTRL()