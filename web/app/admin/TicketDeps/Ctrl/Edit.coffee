define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_Edit'
		@CTRL_AS = 'TicketDepsEdit'
		@DEPS    = ['em', '$scope', 'DepartmentData', 'Api', '$stateParams', '$q']

		init: ->
			@$scope.watch( ->
				return @DepartmentData.deps
			, (newVal) =>
				@initDeplistData(@DepartmentData.deps)
			)

			@loadTicket()

		loadTicket: ->
			waiting = [
				@DepartmentData.loadDepList(),
				@Api.sendDataGet([
					'/ticket_deps/' + @$stateParams.id,
					'/agents',
					'/agentgroups',
					'/usergroups',
					'/ticket_accounts'
				])
			]

			@$q.all(waiting).then( (d) =>
				[departments, data_results] = d

				@initDeplistData(departments)
				@initData(
					data_results.data.api_ticket_deps_get.department,
					{ usergroups: data_results.data.api_ticket_deps_get.perms_usergroup_ids, agentgroups: data_results.data.api_ticket_deps_get.perms_agentgroup_ids, agents: data_results.data.api_ticket_deps_get.perms_agent_ids },
					data_results.data.api_agents_list.agents,
					data_results.data.api_agentgroups_list.agentgroups,
					data_results.data.api_usergroups_list.usergroups
				)
			)

		initData: (dep, department_perms, agents, agentgroups, usergroups) ->
			@dep = @em.createUnmanagedEntity('department', 'id', dep)

			@agentgroups = agentgroups
			for ug in @agentgroups
				for perm in department_perms.agentgroups
					if perm.usergroup_id = ug.id
						ug[perm.perm_name] = true

			@usergroups = usergroups
			for ug in @usergroups
				for ugid in department_perms.usergroups
					if ugid == ug.id
						ug.perm = true

			@agents = agents
			for ag in @agents
				for perm in department_perms.agents
					if perm.agent_id = ag.id
						ag[perm.perm_name] = true

		initDeplistData: (departments) ->
			@departments = departments.values()
			@dep_parent_list = departments.values()
			@dep_parent_list.unshift({ id: 0, title: 'No Parent'})

		saveProperties: ->
			@Api.sendPost('/ticket_deps/' + @dep.id, {
				title: @dep.title,
				user_title: @dep.user_title,
				parent_id: @dep.parent_id || 0
			})

			if @em.hasById('department', @dep.id)
				model = @em.getById('department', @dep.id)
				model.title = @dep.title
				model.user_title = @dep.user_title

			@ngApply()

	Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()