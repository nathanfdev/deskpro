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
			@form = {email_primary: '', emails_list: []}
			@hasPermOverrides = false
			@hasDepOverrides = false
			@primary_phone_number_region = 'US'
			@service =
				agents: @DataService.get 'Agents'
			@all_perms =
				perms: {}
				deps_perms:
					tickets: {assign: true, full: true}
					chat: {full: true}

			@$scope.$watch('EditCtrl.form.emails_list', (emails_list) =>
				@email_sysaccount_error = false
				if not emails_list then return
				if not @form.email_primary or @form.email_primary == '' or emails_list.indexOf(@form.email_primary) == -1
					if emails_list.length
						@form.email_primary = emails_list[0]
					else
						@form.email_primary = ''
			)
			return

		initialLoad: ->
			if @agentId
				promise = @Api.sendDataGet({
					agent: "/agents/#{@agentId}?extended=1",
					teams: "/agent_teams",
					groups: "/agent_groups",
					groupPerms: "/agent_groups/all/permissions",
					notif_prefs_table: "/agents/#{@agentId}/notify-prefs/get-tables",
					ticketDeps: "/ticket_deps?with_perms=1",
					chatDeps: "/chat_deps?with_perms=1",
					default_country: "/settings/values/core.default_country_code"
				})
			else
				promise = @Api.sendDataGet({
					teams: "/agent_teams",
					groups: "/agent_groups",
					groupPerms: "/agent_groups/all/permissions",
					notif_prefs_table: "/agents/0/notify-prefs/get-tables",
					ticketDeps: "/ticket_deps?with_perms=1",
					chatDeps: "/chat_deps?with_perms=1",
					default_country: "/settings/values/core.default_country_code"
				})

			promise.then( (result) =>
				if @agentId
					@agent = result.data.agent.agent
					@agent.signature_html = result.data.agent.signature_html
					@perm_form = result.data.agent.perms
					@primary_phone_number_region = result.data.default_country.value
				else
					@agent = {
						id: 0,
						name: '',
						email: {},
						teams: [],
						usergroups: []
					}
					@perm_form = null

				@primary_phone_number_region = result.data.default_country.value

				@teams  = result.data.teams.agent_teams
				@groups = result.data.groups.groups
				@groupPerms = result.data.groupPerms.groups

				@ticketDeps = result.data.ticketDeps.departments
				@chatDeps   = result.data.chatDeps.departments

				@agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table)
				@notif_prefs = @agentNotifPrefsModel.prefsTable

				@agentFormModel = new EditAgentModel(@agent, @groups, @teams, @primary_phone_number_region)
				@form = @agentFormModel.form

				@$scope.$watch('EditCtrl.form.agent_groups', =>
					@updateEffectiveUgPerms()
					@updateAllPermsState()
				, true)

				#--------------------
				# Departments
				#--------------------

				@deps_perms = {
					tickets: {},
					chat: {}
				}

				for dep in @ticketDeps
					assign = false
					full = false

					if @agentId and dep.permissions?.users
						u = dep.permissions.users.filter((x) => x.id == @agentId)[0]
						if u
							if u.name == 'full' then full = true else assign = true

					@deps_perms.tickets[dep.id] = { assign: assign, full: full }

				for dep in @chatDeps
					full = false
					if @agentId and dep.permissions?.users
						u = dep.permissions.users.filter((x) => x.id == @agentId)[0]
						if u
							full = true

					@deps_perms.chat[dep.id] = { full: full }

				@updateHasPermOverridesStatus()
			)
			return promise



		changeAllPerms: (type, section) ->
			return if !@perm_form? || !@deps_perms?

			if 'perms' == type
				for perm of @perm_form[section]
					if not @ugEffectivePerms[section]?[perm]? or not @ugEffectivePerms[section][perm]
						@perm_form[section][perm] = @all_perms[type][section]

				if 'people' == section
					@changeAllPerms('perms', 'org')

			else if 'deps_perms_tickets' == type
				for dep of @deps_perms.tickets
					if !@ugEffectiveDepPerms.tickets[dep]?[section]? || !@ugEffectiveDepPerms.tickets[dep][section]
						@deps_perms.tickets[dep][section] = @all_perms.deps_perms.tickets[section]

			else if 'deps_perms_chat' == type
				for dep of @deps_perms.chat
					if !@ugEffectiveDepPerms.chat[dep]?[section]? || !@ugEffectiveDepPerms.chat[dep][section]
						@deps_perms.chat[dep][section] = @all_perms.deps_perms.chat[section]

			@updateHasPermOverridesStatus()



		updateAllPermsState: ->
			return if !@perm_form?

			for section, perms of @perm_form
				enabled = true
				for perm of perms
					if !perms[perm] && !@ugEffectivePerms[section]?[perm]
						enabled = false
						break
				@all_perms.perms[section] = enabled

			for type, sections of @all_perms.deps_perms
				for section of sections
					enabled = true
					for dep of @deps_perms[type]
						if !@deps_perms[type][dep][section] && !@ugEffectiveDepPerms[type][dep][section]
							enabled = false
					@all_perms.deps_perms[type][section] = enabled



		###
		# When usergroups are changed, we need to update the effective list of permissions
		###
		updateEffectiveUgPerms: ->

			# todo this map should be loaded from server
			@ugEffectivePerms = {
				ticket: {},
				people: {},
				org: {},
				chat: {},
				publish: {},
				general: {},
				tasks: {}
			}

			@ugEffectiveDepPerms = {
				tickets: {},
				chat: {}
			}

			if not @form.agent_groups then return

			groupIds = []
			for group in @form.agent_groups
				if group.value
					groupIds.push(group.id)

			for dep in @ticketDeps
				assign = false
				full = false

				if dep.permissions?.agentgroups
					perms = dep.permissions.agentgroups.filter((x) -> x.id in groupIds)
					for p in perms
						if p.name == 'full' then full = true else assign = true

				@ugEffectiveDepPerms.tickets[dep.id] = { assign: assign, full: full }

			for dep in @chatDeps
				full = false

				if dep.permissions?.agentgroups
					perms = dep.permissions.agentgroups.filter((x) -> x.id in groupIds)
					for p in perms
						full = true

				@ugEffectiveDepPerms.chat[dep.id] = { full: full }

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
			return if !@ugEffectivePerms? || !@ugEffectiveDepPerms?

			@hasPermOverrides = false
			run = =>
				for own type, perms of @perm_form
					for own permName, value of perms
						if value
							if not @ugEffectivePerms[type]?[permName]? or not @ugEffectivePerms[type][permName]
								@hasPermOverrides = true
								return
			run()

			@hasDepOverrides = false
			run = =>
				return if not @deps_perms or not @deps_perms.tickets
				for app in ['tickets', 'chat']
					for own depId, perms of @deps_perms[app]
						for own perm, value of perms
							if value
								if not @ugEffectiveDepPerms[app][depId][perm]
									@hasDepOverrides = true
									return
			run()

			@updateAllPermsState()


		###
    	# This does the actual removal of all perm overrides
		###
		clearPermOverrides: =>
			for own type, perms of @perm_form
				for own permName, value of perms
					perms[permName] = false
			@hasPermOverrides = false

		###
    	# This does the actual removal of all depoverrides
		###
		clearDepOverrides: =>
			for app in ['tickets', 'chat']
				for own depId, perms of @deps_perms[app]
					for own perm, value of perms
						@deps_perms[app][depId][perm] = false
			@hasDepOverrides = false


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
							doReset($scope.password.manual).then(=>
								$modalInstance.close()
							, -> $scope.is_saving = false)
						else
							doReset(false).then(=>
								$modalInstance.close()
							, -> $scope.is_saving = false)
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
					notif_prefs_table: "/agents/#{settings.agent_id}/notify-prefs/get-tables",
					teams: "/agent_teams",
					groups: "/agent_groups",
				}).then( (result) =>
					agent  = result.data.agent.agent
					teams  = result.data.teams.agent_teams
					groups = result.data.groups.groups

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
							tids.push(team.id)
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
								for r, rkey in  @notif_prefs.subs[n].rows
									for c, ckey in r.cols
										for subc, subckey in c
											val = notif_prefs.subs[n]?.rows[rkey]?.cols[ckey]?[subckey]?.value || false
											@notif_prefs.subs[n].rows[rkey].cols[ckey][subckey].value = val

					if settings.other_notifs
						for n in ['chat', 'task', 'twitter', 'feedback', 'publish', 'crm', 'account']
							if @notif_prefs.subs[n]? and notif_prefs.subs[n]?
								for r, rkey in  @notif_prefs.subs[n].rows
									for subc, subckey in r.cols
										val = notif_prefs.subs[n]?.rows[rkey]?.cols[subckey]?.value || false
										@notif_prefs.subs[n].rows[rkey].cols[subckey].value = val
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
					# todo
					@service.agents.get(@agentId).then (agent) =>
						@service.agents._removeModel agent
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
			})

		###
    	# Shows the copy settings modal
    	###
		showEditProfile: ->
			@$modal.open({
				templateUrl: @getTemplatePath('Agents/edit-profile-modal.html'),
				controller: 'Admin_Agents_Ctrl_EditProfile',
				resolve: {
					agent: =>
						return @agent
					saveMethod: =>
						return (data, from) =>
							if from.new_image
								@agent.picture_blob = from.new_image
							else if from.form.picture_set == 'default'
								@agent.picture_blob = null

							@agent.timezone = from.form.timeone
							@agent.signature_html = from.form.signature_html

							if @agentId
								return @Api.sendPostJson("/agents/#{@agentId}/profile", data)
							else
								@pendingProfileData = data
				}
			})


		###
    	# Returns an object hash of the complete form data
		###
		getFormData: ->
			formData = {
				agent:              @agentFormModel.getFormData(),
				filter_subs:        @agentNotifPrefsModel.getFilterSubs(),
				other_subs:         @agentNotifPrefsModel.getOtherSubs(),
				perm_overrides:     @perm_form
				dep_perm_overrides: @deps_perms
			}

			if @pendingProfileData
				formData.profile = @pendingProfileData
				@pendingProfileData = null

			return formData


		###
    	# Saves the agent
		###
		saveAgent: ->
			if not @$scope.form_props.$valid
				return

			@email_dupe_error = false
			@email_sysaccount_error = false
			@invalid_phone_error = false
			@startSpinner('saving')

			postData = @getFormData()

			if @agentId
				promise = @Api.sendPostJson("/agents/#{@agentId}", postData)
			else
				promise = @Api.sendPutJson("/agents", postData)

			promise.then( (res) =>
				# todo
				@service.agents._addModel res.data.agent
				@agent.display_name = @form.name

				if !@agentId
					@$state.go('agents.agents.edit', {id: res.data.person_id})

				@stopSpinner('saving')
			, (res) =>
				if res?.data?.error_code == 'dupe_email'
					@email_dupe_error = res.data.error_info.existing
				if res?.data?.error_code == 'system_email_addresses'
					@email_sysaccount_error = res.data.error_info.emails.join(', ')
				if res?.data?.error_code == 'invalid_phone_number'
					@invalid_phone_error = res.data.error_message + ': ' + res.data.error_info.primary_phone_number_text

				@stopSpinner('saving', true)
				@applyErrorResponseToView(res)
			)

			return promise

	Admin_Agents_Ctrl_Edit.EXPORT_CTRL()
