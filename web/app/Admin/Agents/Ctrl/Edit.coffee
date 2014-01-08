define [
	'DeskPRO/Util/Strings',
	'Admin/Main/Ctrl/Base',
	'Admin/Agents/FormModel/EditAgentModel',
	'Admin/Agents/FormModel/EditAgentNotifPrefs'
], (
	Strings,
	Admin_Ctrl_Base,
	EditAgentModel,
	EditAgentNotifPrefs
) ->
	class Admin_Agents_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = []

		init: ->
			@agentId = parseInt(@$stateParams.id)
			@form = {}
			@hasPermOverrides = false
			return

		initialLoad: ->
			if @agentId
				promise = @Api.sendDataGet({
					agent: "/agents/#{@agentId}",
					teams: "/agent_teams",
					groups: "/agentgroups",
					groupPerms: "/agentgroups/permissions",
					notif_prefs_table: "/agents/#{@agentId}/notify-prefs/get-tables"
				})
			else
				promise = @Api.sendDataGet({
					teams: "/agent_teams",
					groups: "/agentgroups",
					groupPerms: "/agentgroups/permissions",
					notif_prefs_table: "/agents/0/notify-prefs/get-tables"
				})

			promise.then( (result) =>
				if @agentId
					@agent = result.data.agent.agent
				else
					@agent = {
						id: 0,
						name: '',
						primary_email: {},
						teams: [],
						usergroups: []
					}

				@teams  = result.data.teams.agent_teams
				@groups = result.data.groups.agentgroups
				@groupPerms = result.data.groupPerms.groups

				@agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table)
				@notif_prefs = @agentNotifPrefsModel.prefsTable

				@agentFormModel = new EditAgentModel(@agent, @groups, @teams)
				@form = @agentFormModel.form

				@$scope.$watch('EditCtrl.form.agent_groups', =>
					@updateEffectiveUgPerms()
				, true)

				@perm_form = @agent.perms
				@updateHasPermOverridesStatus()
			)
			return promise


		###
		# When usergroups are changed, we need to update the effective list of permissions
		###
		updateEffectiveUgPerms: ->
			@ugEffectivePerms = {
				ticket: {},
				people: {},
				org: {},
				chat: {},
				publish: {},
				general: {}
			}

			if not @form.agent_groups then return

			groupIds = []
			for group in @form.agent_groups
				if group.value
					groupIds.push(group.id)

			for info in @groupPerms
				if info.group.id in groupIds
					for own type, perms of info.perms
						for own pname, pval of perms
							if pval
								@ugEffectivePerms[type][pname] = pval


		###
    	# When a permission is updated, we need to update the hasPermOverrides status.
    	# This is done by an ngChange on the permission toggles. We dont use a watch because
    	# it can become too slow to watch the large graph of permissions.
		###
		updateHasPermOverridesStatus: ->
			@updateEffectiveUgPerms()

			@hasPermOverrides = false
			for own type, perms of @perm_form
				for own permName, value of perms
					if value
						if not @ugEffectivePerms[type]?[permName]? or not @ugEffectivePerms[type][permName]
							@hasPermOverrides = true
							return


		###
    	# This does the actual removal of all perm overrides
		###
		clearPermOverrides: =>
			for own type, perms of @perm_form
				for own permName, value of perms
					perms[permName] = false
			@hasPermOverrides = false


		###
    	# Shows the password reset modal
    	###
		showResetPassword: ->
			doReset = (setPassword) =>
				if not setPassword or not Strings.trim(setPassword)
					setPassword = ''

				return @Api.sendPostJson("/agents/#{@agentId}/reset-password", {
					set_password: setPassword
				})

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Agents/reset-password-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.password = {
						mode: 'random',
						manual: ''
					}

					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.saveResetPassword = ->
						$scope.is_saving = true
						if $scope.password.mode == 'set'
							doReset($scope.password.manual).then(=> $modalInstance.close())
						else
							doReset(false).then(=> $modalInstance.close())
				]
			});

			return inst

		###
    	# Shows the copy settings modal
    	###
		showCopySettings: ->

			#------------------------------
			# Get agent options
			#------------------------------

			# The list pane is open right now and has the list of agents we can use
			agents = @$scope.$parent?.ListCtrl?.agents
			if not agents then return false

			if agents.length == 1
				@showAlert('There are no other agents to copy settings from')
				return false

			# Dont include ourself in the list
			agents = agents.filter((x) => x.id != @agentId)

			#------------------------------
			# Function callback that loads and applies the settings
			#------------------------------

			copySettings = (settings) =>
				promise = @Api.sendDataGet({
					agent: "/agents/#{settings.agent_id}",
					notif_prefs_table: "/agents/#{@agentId}/notify-prefs/get-tables",
					teams: "/agent_teams",
					groups: "/agentgroups",
				}).then( (result) =>
					agent  = result.data.agent.agent
					teams  = result.data.teams.agent_teams
					groups = result.data.groups.agentgroups

					agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table)
					notif_prefs = agentNotifPrefsModel.prefsTable

					agentFormModel = new EditAgentModel(agent, groups, teams)
					form = agentFormModel.form

					if settings.zones
						@form.zones.admin   = form.zones.admin
						@form.zones.reports = form.zones.reports

					if settings.teams
						tids = []
						for team in form.teams
							if team.value then tids.push(team.id)
						for team in @form.teams
							team.value = team.id in tids

					if settings.groups
						gids = []
						for group in form.agent_groups
							if group.value then gids.push(group.id)
						for group in @form.agent_groups
							group.value = group.id in gids

					if settings.perms
						for own type, perms of agent.perms
							for own permName, value of perms
								continue if not @perm_form[type]?[permName]?
								@perm_form[type][permName] = value

					if settings.ticket_notifs
						for n in ['sys_filters_email', 'sys_filters_alert', 'custom_filters_email', 'custom_filters_alert']
							if @notif_prefs.subs[n]? and notif_prefs.subs[n]?
								@notif_prefs.subs[n] = notif_prefs.subs[n]

					if settings.other_notifs
						for n in ['chat', 'task', 'twitter', 'feedback', 'publish', 'crm', 'account']
							if @notif_prefs.subs[n]? and notif_prefs.subs[n]?
								@notif_prefs.subs[n] = notif_prefs.subs[n]
				)
				return promise

			#------------------------------
			# Show the modal
			#------------------------------

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Agents/copy-settings-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.agents = agents
					$scope.options = {
						agent_id: agents[0].id+"",
						zones: false,
						teams: false,
						groups: false,
						perms: false,
						ticket_notifs: false,
						other_notifs: false
					}

					$scope.doCopySettings = (settings) ->
						$scope.is_loading = true
						copySettings(settings).then(->
							$modalInstance.dismiss()
						)
				]
			});


		###
    	# Shows the copy settings modal
    	###
		showLoginAs: ->
			agentName = @form.name
			agentId = @agentId
			Api = @Api

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Agents/login-as-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.agentName = agentName
					$scope.is_loading = true

					Api.sendGet("/agents/#{agentId}/login-token").then( (res) ->
						$scope.is_loading = false
						$scope.login_token = res.data.login_token
					)
				]
			});

			inst.result.then(=>

			)


		###
    	# Shows the copy settings modal
    	###
		showDelete: ->

			deleteAgent = (settings) =>
				if settings.method == 'user'
					target = "/agents/#{@agentId}/delete/to-user"
				else
					target = "/agents/#{@agentId}/delete"

				p = @Api.sendDelete(target)
				p.then(=>
					if @$scope.$parent.ListCtrl? then @$scope.$parent.ListCtrl.removeAgentFromList(@agentId)
					@$state.go('agents.agents')
				)

				return p

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Agents/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.options = {
						method: 'user'
					}

					$scope.doDelete = (options) ->
						$scope.is_loading = true
						deleteAgent(options).then(-> $modalInstance.dismiss())
				]
			});


		###
    	# Returns an object hash of the complete form data
		###
		getFormData: ->
			formData = {
				agent:           @agentFormModel.getFormData(),
				filter_subs:     @agentNotifPrefsModel.getFilterSubs(),
				other_subs:      @agentNotifPrefsModel.getOtherSubs(),
				perm_overrides:  @perm_form
			}

			return formData


		###
    	# Saves the agent
		###
		saveAgent: ->
			@startSpinner('saving')

			postData = @getFormData()

			if @agentId
				promise = @Api.sendPostJson("/agents/#{@agentId}", postData)
			else
				promise = @Api.sendPutJson("/agents", postData)

			promise.then( (res) =>
				@agent.display_name = @form.name

				if @agentId
					if @$scope.$parent.ListCtrl? then @$scope.$parent.ListCtrl.updateAgent(@agent)
				else
					@$state.go('agents.agents.edit', {id: res.data.person_id})
					if @$scope.$parent.ListCtrl? then @$scope.$parent.ListCtrl.addAgent(res.data.person_id, @agent.display_name)

				@stopSpinner('saving')
			)

			return promise

	Admin_Agents_Ctrl_Edit.EXPORT_CTRL()