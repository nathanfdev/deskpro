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

			@loadDepartment()


		###*
		# Load (or reload) the page values
		###
		loadDepartment: ->
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


		###*
		# Init data from loadDepartment, getting it ready for use
		###
		initData: (dep, department_perms, agents, agentgroups, usergroups) ->
			if not dep.parent_id then dep.parent_id = 0
			@dep = @em.createUnmanagedEntity('department', 'id', dep)

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
			@dep_parent_list = departments.values()
			@dep_parent_list.unshift({ id: 0, title: 'No Parent'})


		###*
		# Save the Properties part of the form
		###
		saveProperties: ->
			@Api.sendPostJson('/ticket_deps/' + @dep.id, {
				properties: @dep.getData()
			})

			if @em.hasById('department', @dep.id)
				model = @em.getById('department', @dep.id)
				model.title = @dep.title
				model.user_title = @dep.user_title

			@ngApply()


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

			@Api.sendPostJson('/ticket_deps/' + @dep.id, {
				permissions: perms
			})

	Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()