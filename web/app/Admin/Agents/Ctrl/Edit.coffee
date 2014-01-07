define [
	'Admin/Main/Ctrl/Base',
	'Admin/Agents/FormModel/EditAgentModel',
	'Admin/Agents/FormModel/EditAgentNotifPrefs'
], (
	Admin_Ctrl_Base,
	EditAgentModel,
	EditAgentNotifPrefs
) ->
	class Admin_Agents_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = []

		init: ->
			@agentId = @$stateParams.id;
			@form = {}
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				agent: "/agents/#{@agentId}",
				teams: "/agent_teams",
				groups: "/agentgroups",
				groupPerms: "/agentgroups/permissions",
				notif_prefs_table: "/agents/#{@agentId}/notify-prefs/get-tables"
			}).then( (result) =>
				@agent  = result.data.agent.agent
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
			)
			return

		updateEffectiveUgPerms: =>
			@ugEffectivePerms = {
				ticket: {},
				people: {},
				org: {},
				chat: {},
				publish: {},
				general: {}
			}

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


		getFormData: ->
			formData = {
				agent:           @agentFormModel.getFormData(),
				filter_subs:     @agentNotifPrefsModel.getFilterSubs(),
				other_subs:      @agentNotifPrefsModel.getOtherSubs(),
				perm_overrides:  @perm_form
			}

			return formData

		saveAgent: ->
			@startSpinner('saving')

			postData = @getFormData()

			if @agentId
				promise = @Api.sendPostJson("/agents/#{@agentId}", postData)
			else
				promise = @Api.sendPutJson("/agents", postData)

			promise.then(=>
				@stopSpinner('saving')
			)

	Admin_Agents_Ctrl_Edit.EXPORT_CTRL()