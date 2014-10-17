define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_AgentTeams_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentTeams_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$upload', '$http']

		init: ->
			@teamId = parseInt(@$stateParams.id)
			@enable_avatar = false

			@$scope.icon_image = null
			@$scope.$on 'icon.selected', (e, path) => @selectIcon path

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

				@setAvatar @team.avatar

				# value=true on agents that are members
				memberIds = @team.members.map((x) -> x.id)
				@agents.map((x) -> if x.id in memberIds then x.value = true)
			)
			return promise



		setAvatar: (blob) =>
			@team.avatar = blob
			if !blob?
				@$scope.icon_image = null
				@enable_avatar = false
			else
				@$scope.icon_image = blob.thumbnail_url_50
				@enable_avatar = true



		onFileSelect: (files) ->
			@$scope.uploading = false
			file = files[0]

			@$upload.upload({
				url: @$http.formatApiUrl('/misc/upload'),
				data: { is_image: true },
				file: file
			}).success( (data) =>
				@$scope.uploading = false
				@setAvatar data.blob
			).error( (data) =>
				@$scope.uploading = false
				@Growl.error data?.error_message || 'Error'
			)



		selectIcon: (image) =>
			setAvatar null if !image?

			@$scope.uploading = true
			@Api.sendPostJson('/misc/upload', {path: image, is_image: true}).then(
				(data) =>
					@$scope.uploading = false
					@setAvatar data.data.blob
				() =>
					@$scope.uploading = false
			)



		saveForm: ->
			postData = {
				team: {
					name: @team.name
					person_ids: []
				}
			}

			if @enable_avatar
				postData.team.avatar = @team.avatar?.id || null
			else
				@avatar = null

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