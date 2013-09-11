define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_Edit'
		@CTRL_AS = 'TicketDepsEdit'
		@DEPS    = ['em', '$scope', 'DepartmentData', 'Api', '$stateParams', '$q']

		init: ->
			@$scope.watch( ->
				return @DepartmentData.deps
			, (newVal) ->
				@departments = @DepartmentData.deps.values()
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
				@departments = departments.values()

				@dep = @em.createEntity('department', 'id', data_results.data.api_ticket_deps_get.department)
				@dep_ug_perms = data_results.data.api_ticket_deps_get.perms_usergroup_ids
				@dep_ag_perms = data_results.data.api_ticket_deps_get.perms_agentgroup_ids
				@dep_a_perms  = data_results.data.api_ticket_deps_get.perms_agent_ids
			)

		saveDep: ->
			@Api.sendPost('/ticket_deps/' + @dep.id, {
				title: @dep.title,
				user_title: @dep.user_title
			})

			model = @DepartmentData.deps.get(@dep.id)
			model.title = @dep.title
			model.user_title = @dep.user_title
			@ngApply()

	Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()