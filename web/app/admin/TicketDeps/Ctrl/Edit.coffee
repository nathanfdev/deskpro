define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_Edit'
		@CTRL_AS = 'TicketDepsEdit'
		@DEPS    = ['$scope', 'DepartmentData', 'Api', '$stateParams']

		init: ->

			@deps_list = @DepartmentData.deps.values()

			@Api.sendDataGet([
				'/ticket_deps/' + @$stateParams.id
				'/agents',
				'/agentgroups',
				'/usergroups',
				'/ticket_accounts'
			]).success((data) =>
				@dep = data.api_ticket_deps_get.department
				@agents = data.api_agents_list
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