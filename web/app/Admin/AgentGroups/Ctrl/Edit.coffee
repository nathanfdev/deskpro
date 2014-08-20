define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_AgentGroups_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentGroups_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'

		init: ->
			@groupId = parseInt(@$stateParams.id)

			@service =
				groups: @DataService.get 'AgentGroups'
				agents: @DataService.get 'Agents'
				ticketDeps: @DataService.get 'TicketDeps'
				chatDeps: @DataService.get 'ChatDeps'

			@$scope.toggleAgent = (agent) =>
				index = @group.person_ids.indexOf(agent.id)
				groupIndex = agent.agentgroup_ids.indexOf @group

				if index != -1
					@group.person_ids.splice(index, 1)
					agent.agentgroup_ids.splice(groupIndex, 1) if groupIndex != -1
				else
					@group.person_ids.push agent.id
					agent.agentgroup_ids.push @group.id if groupIndex == -1

			return



		initialLoad: ->

			promises = [@service.groups.get(@groupId), @service.agents.all(), @service.ticketDeps.all(), @service.chatDeps.all()]

			@$q.all(promises).then (res) =>
				@group = res[0] || {id: 0}
				@agents = res[1]
				@ticketDeps = res[2]
				@chatDeps   = res[3]

				@group.person_ids = []
				@assignDepsPerms @group

				@agents.map (agent) =>
					@group.person_ids.push agent.id if -1 != agent.agentgroup_ids.indexOf @group.id

				if @group.sys_name == 'agent_all_perms' or @group.sys_name == 'agent_all_safe_perms'
					@$scope.all_locked_perms = true



		# todo load from controller
		assignDepsPerms: (group) ->

			group.deps_perms = {
				tickets: {},
				chat: {}
			}

			for dep in @ticketDeps
				assign = false
				full = false

				if dep.permissions?.agentgroups
					u = dep.permissions.agentgroups.filter((x) => x.id == group.id)[0]
					if u
						if u.name == 'full' then full = true else assign = true

				group.deps_perms.tickets[dep.id] = { assign: assign, full: full }

			for dep in @chatDeps
				full = false
				if dep.permissions?.agentgroups
					u = dep.permissions.agentgroups.filter((x) => x.id == group.id)[0]
					if u
						full = true

				group.deps_perms.chat[dep.id] = { full: full }

				

		saveForm: ->
			postData =
				group: @group
				dep_perms: @group.deps_perms

			if @groupId
				p = @sendFormSaveApiCall('POST', "/agent_groups/#{@groupId}", postData)
			else
				p = @sendFormSaveApiCall('PUT', "/agent_groups", postData)

			p.then( (res) =>
				@Growl.success(@getRegisteredMessage('saved_group'))

				if !@groupId
					@groupId = @group.id = res.data.group_id
					@service.groups._addModel @group

				# force reload deps perms
				# todo move deps perms as @group attribute
				@$q.all [@service.ticketDeps.all(true), @service.chatDeps.all(true)]

				@$state.go('agents.groups.edit', {id: @groupId})
			)
			return



		showDelete: ->
			deleteGroup = =>
				@service.groups.remove(@group).then =>
					# force reload deps perms
					# todo move deps perms as @group attribute
					@$q.all [@service.ticketDeps.all(true), @service.chatDeps.all(true)]
					@$state.go('agents.groups')

			inst = @$modal.open({
				templateUrl: @getTemplatePath('AgentGroups/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doDelete = (options) ->
						$scope.is_loading = true
						deleteGroup().then(-> $modalInstance.dismiss())
				]
			});



		###
    # Shows the copy settings modal
    ###
		showCopySettings: ->

			groups = []

			#------------------------------
			# Function callback that loads and applies the settings
			#------------------------------

			copySettings = (settings) =>

				@service.groups.get(settings.group_id).then (group) =>
					return if !group?

					if settings.copy_perms
						@group.perms = {}
						angular.copy group.perms, @group.perms

					if settings.copy_deps_perms
						@assignDepsPerms group
						@group.deps_perms = {}
						angular.copy group.deps_perms, @group.deps_perms

			#------------------------------
			# Show the modal
			#------------------------------

			@service.groups.all().then (list) =>
				list.map (group) => groups.push group if group != @group

				if !groups.length
					return @showAlert('There are no other groups to copy permissions from')

				inst = @$modal.open({
					templateUrl: @getTemplatePath('AgentGroups/copy-perms-modal.html'),
					controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->

						$scope.groups = groups
						$scope.options =
							group_id: groups[0].id
							copy_perms: true
							copy_deps_perms: true

						$scope.dismiss = -> $modalInstance.dismiss()

						$scope.doCopySettings = ->
							$scope.is_loading = true
							copySettings($scope.options).then -> $modalInstance.dismiss()
					]
				});



	Admin_AgentGroups_Ctrl_Edit.EXPORT_CTRL()