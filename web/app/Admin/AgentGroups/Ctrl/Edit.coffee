define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_AgentGroups_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentGroups_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'

		init: ->
			@groupId = parseInt(@$stateParams.id)
			return

		initialLoad: ->
			if @groupId
				promise = @Api.sendDataGet({
					group: "/agent_groups/#{@groupId}",
					agents: "/agents",
					ticketDeps: "/ticket_deps?with_perms=1",
					chatDeps: "/chat_deps?with_perms=1"
				})
			else
				promise = @Api.sendDataGet({
					agents: "/agents",
					ticketDeps: "/ticket_deps?with_perms=1",
					chatDeps: "/chat_deps?with_perms=1"
				})

			promise.then( (res) =>
				@agents = res.data.agents.agents

				if @groupId
					@group  = res.data.group.group
				else
					@group = { id: 0, title: '', members: [] }

				# value=true on agents that are members
				memberIds = @group.members.map((x) -> x.id)
				@agents.map((x) -> if x.id in memberIds then x.value = true)

				@perm_form = @group.perms

				#--------------------
				# Departments
				#--------------------

				@ticketDeps = res.data.ticketDeps.departments
				@chatDeps   = res.data.chatDeps.departments

				@deps_perms = {
					tickets: {},
					chat: {}
				}

				if @groupId
					for dep in @ticketDeps
						assign = false
						full = false

						if dep.permissions?.agentgroups
							u = dep.permissions.agentgroups.filter((x) => x.id == @groupId)[0]
							if u
								if u.name == 'full' then full = true else assign = true

						@deps_perms.tickets[dep.id] = { assign: assign, full: full }

					for dep in @chatDeps
						full = false
						if dep.permissions?.agentgroups
							u = dep.permissions.agentgroups.filter((x) => x.id == @groupId)[0]
							if u
								full = true

						@deps_perms.chat[dep.id] = { full: full }
			)
			return promise

		saveForm: ->
			postData = {
				group: {
					title: @group.title,
					perms: @perm_form,
					person_ids: []
				},
				dep_perms: @deps_perms
			}

			for a in @agents
				if a.value
					postData.group.person_ids.push(a.id)

			if @groupId
				p = @sendFormSaveApiCall('POST', "/agent_groups/#{@groupId}", postData)
			else
				p = @sendFormSaveApiCall('PUT', "/agent_groups", postData)

			p.then( (res) =>
				@Growl.success(@getRegisteredMessage('saved_group'))

				if @groupId
					@getGroupListCtrl().renameGroupById(@groupId, @group.title)
				else
					@groupId = res.data.group_id
					@getGroupListCtrl().addGroup({ id: @groupId, title: @group.title})
					@$state.go('agents.groups.edit', {id: @groupId})
			)
			return

		###
    	# Shows the copy settings modal
    	###
		showDelete: ->
			deleteGroup = =>
				p = @Api.sendDelete("/agent_groups/#{@groupId}")
				p.then(=>
					@getGroupListCtrl().removeGroupById(@groupId)
					@$state.go('agents.agents')
				)
				return p

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
    	# Gets a reference to the parent list view which we need to update with the new details
		###
		getGroupListCtrl: ->
			if @$scope.$parent?.ListCtrl?
				return @$scope.$parent.ListCtrl
			else
				# mock since list isnt there yet
				return {
					addGroup:        -> return
					removeGroupById: -> return
					renameGroupById: -> return
				}

	Admin_AgentGroups_Ctrl_Edit.EXPORT_CTRL()