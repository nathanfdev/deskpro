define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_Edit'
		@CTRL_AS = 'TicketDepsEdit'
		@DEPS    = ['em', '$scope', 'DepartmentData', 'Api', '$stateParams', '$q', '$modal', '$state']

		init: ->
			@addManagedListener(@DepartmentData.deps, 'changed', =>
				@initDeplistData(@DepartmentData.deps)
				@ngApply()
			)

			@loadDepartment()


		###*
		# Load (or reload) the page values
		###
		loadDepartment: ->
			waiting = [
				@DepartmentData.loadDepList(),
				if @$stateParams.id
					@Api.sendDataGet([
						'/ticket_deps/' + @$stateParams.id,
						'/agents',
						'/agentgroups',
						'/usergroups',
						'/ticket_accounts'
					])
				else
					@Api.sendDataGet([
						'/agents',
						'/agentgroups',
						'/usergroups',
						'/ticket_accounts'
					])
			]

			@$q.all(waiting).then( (d) =>
				[departments, data_results] = d

				if @$stateParams.id
					dep_data = data_results.data.api_ticket_deps_get
				else
					dep_data = { department: {}, perms_usergroup_ids: [], perms_agentgroup_ids: [], perms_agent_ids: [] }

				dep = dep_data.department
				if not dep.parent_id then dep.parent_id = 0
				@dep = @em.createUnmanagedEntity('department', 'id', dep)

				@initDeplistData(departments)
				@initData(
					{ usergroups: dep_data.perms_usergroup_ids, agentgroups: dep_data.perms_agentgroup_ids, agents: dep_data.perms_agent_ids },
					data_results.data.api_agents_list.agents,
					data_results.data.api_agentgroups_list.agentgroups,
					data_results.data.api_usergroups_list.usergroups
				)
			)


		###*
		# Init data from loadDepartment, getting it ready for use
		###
		initData: (department_perms, agents, agentgroups, usergroups) ->
			@agentgroups = agentgroups
			for ug in @agentgroups
				for perm in department_perms.agentgroups
					if perm.usergroup_id == ug.id
						ug[perm.perm_name] = true


			@usergroups = usergroups
			for ug in @usergroups
				for perm in department_perms.usergroups
					if perm.usergroup_id == ug.id
						ug[perm.perm_name] = true

			@agents = agents
			for ag in @agents
				for perm in department_perms.agents
					if perm.agent_id == ag.id
						ag[perm.perm_name] = true

		initDeplistData: (departments) ->
			@departments = departments.values()

			if not @dep then return

			@dep_parent_list = departments.values()
			@dep_parent_list = [{
				id: 0,
				title: 'No Parent'
			}]
			for dep in @departments
				if @dep.id != dep.id and not dep.parent_id
					 @dep_parent_list.push(dep)


		###*
		* Get the move dep list for use in the delete/move dlg
    	* @return {Array}
		###
		getMoveDepList: ->
			dep_move_list = []
			for dep in @departments
				 if @dep.id != dep.id
					 if not dep._child_ids
					 	dep_move_list.push(dep)

			return dep_move_list


		###*
		# Save the Properties part of the form
		###
		saveProperties: ->
			if @dep.id
				promise = @Api.sendPostJson('/ticket_deps/' + @dep.id, {
					properties: @dep.getData()
				})
			else
				promise = @Api.sendPostJson('/ticket_deps/create', {
					properties: @dep.getData()
				})

			# If the department is new, we need to handle updating the UI
			# with the newly saved department once the request comes back with an ID
			if @em.hasById('department', @dep.id)
				model = @em.getById('department', @dep.id)
				model.title = @dep.title
				model.user_title = @dep.user_title
			else
				promise.success( (result) =>
					@dep.id = result.id

					model = @em.createEntity('department', 'id', @dep.getData())
					@DepartmentData.addToList(model)
					@initDeplistData(@DepartmentData.deps)
				)

			return promise


		###*
		# Save the Permissions sections of the form
		###
		savePermissions: (type = 'all') ->

			perms = {}

			if type == 'all' || type == 'agents'
				perms.agents = []
				for agent in @agents
					if agent.full
						perms.agents.push({
							agent_id: agent.id,
							perm_name: 'full'
						})
					else if agent.assign
						perms.agents.push({
							agent_id: agent.id,
							perm_name: 'assign'
						})

			if type == 'all' || type == 'agentgroups'
				perms.agentgroups = []
				for agentgroup in @agentgroups
					if agentgroup.full
						perms.agentgroups.push({
							usergroup_id: agentgroup.id,
							perm_name: 'full'
						})
					else if agentgroup.assign
						perms.agentgroups.push({
							usergroup_id: agentgroup.id,
							perm_name: 'assign'
						})

			if type == 'all' || type == 'usergroups'
				perms.usergroups = []
				for usergroup in @usergroups
					if usergroup.use
						perms.usergroups.push({
							usergroup_id: usergroup.id,
							perm_name: 'use'
						})

			return @Api.sendPostJson('/ticket_deps/' + @dep.id, {
				permissions: perms
			})


		###*
		# Show the delete dlg
		###
		startDelete: ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketDeps/delete-modal.html'),
				controller: ['$scope', '$modalInstance', 'move_deps_list', ($scope, $modalInstance, move_deps_list) ->
					$scope.selected = {
						move_to_id: "0"
					}
					$scope.move_deps_list = move_deps_list

					$scope.confirm = ->
						console.log($scope.selected.move_to_id)
						$modalInstance.close($scope.selected.move_to_id);

					$scope.dismiss = ->
						$modalInstance.dismiss();
				],
				resolve: {
					move_deps_list: =>
						return @getMoveDepList()
				}
			});

			inst.result.then( (move_to) =>
				@deleteDepartment(move_to)
			)

		###*
		# Actually do th edelete
		###
		deleteDepartment: (move_to) ->
			@Api.sendDelete('/ticket_deps/' + @dep.id, {
				move_to: move_to
			}).success( =>
				@DepartmentData.deps.remove(@dep.id)
				@em.removeById('department', @dep.id)
				@$state.go('settings.ticket_deps')
			)


	Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()