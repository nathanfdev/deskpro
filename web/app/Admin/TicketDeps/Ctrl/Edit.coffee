define [
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Model/DepAgentPermMatrix'
], (
	Admin_Ctrl_Base,
	Admin_Main_Model_DepAgentPermMatrix
) ->
	class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketDeps_Ctrl_Edit'
		@CTRL_AS   = 'TicketDepsEdit'
		@CTRL_TYPE = 'page'
		@DEPS      = ['em', '$scope', 'DepartmentData', 'Api', '$stateParams', '$q', '$state', '$templateCache', 'Growl']

		init: ->
			@addManagedListener(@DepartmentData.deps, 'changed', =>
				@initDeplistData(@DepartmentData.deps)
				@ngApply()
			)

			@$scope.$watch('TicketDepsEdit.dep.parent_id', (newVal) =>
				newVal = parseInt(newVal)
				if not newVal then return

				parent = @DepartmentData.deps.get(newVal)
				if parent and not parent._child_ids?.length
					@$scope.show_parent_warning = parent
				else
					@$scope.show_parent_warning = false
			)

		initialLoad: ->
			return @loadDepartment()

		checkDirtyState: ->
			if @dep.getChangedFields().length
				return true

			#if not angular.equals(@depPerms, @getPermsData())
			#	return true

			return false

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

			promise = @$q.all(waiting).then( (d) =>
				[departments, data_results] = d

				if @$stateParams.id
					dep_data = data_results.data.api_ticket_deps_get
				else
					dep_data = { department: {}, perms_usergroup_ids: [], perms_agentgroup_ids: [], perms_agent_ids: [] }

				dep = dep_data.department
				if not dep.parent_id then dep.parent_id = 0
				@dep = @em.createUnmanagedEntity('department', 'id', dep)

				@dep._enable_user_title = !!@dep.user_title

				@initDeplistData(departments)
				@initData(
					{ usergroups: dep_data.perms_usergroup_ids, agentgroups: dep_data.perms_agentgroup_ids, agents: dep_data.perms_agent_ids },
					data_results.data.api_agents_list.agents,
					data_results.data.api_agentgroups_list.agentgroups,
					data_results.data.api_usergroups_list.usergroups
				)
			)

			return promise

		###*
		# Init data from loadDepartment, getting it ready for use
		###
		initData: (department_perms, agents, agentgroups, usergroups) ->
			matrix = new Admin_Main_Model_DepAgentPermMatrix()

			for group in agentgroups
				matrix.addGroup(group, [])

			for agent in agents
				matrix.addAgent(agent, [])

			matrix.initPerms()
			@agent_perms = matrix

			for name in ['link', 'win', 'embed']
				tpl = @getTemplatePath("TicketDeps/code-"+name+".html")
				code = @$templateCache.get(tpl).replace(/%DEPID%/g, @dep.id)
				@$scope['code_' + name] = code

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
		# Save everything
		###
		saveAll: ->
			if not @dep._enable_user_title
				@dep.user_title = ''

			props = @getPropsData()
			props.move_tickets_to = 'self'

			postData = {
				properties: props,
				permissions: @getPermsData()
			}

			if @dep.id
				is_new = false
				promise = @Api.sendPostJson('/ticket_deps/' + @dep.id, postData)
			else
				is_new = true
				promise = @Api.sendPostJson('/ticket_deps/create', postData)

			if @dep.parent_id and @dep.parent_id != "0"
				parent = @DepartmentData.deps.get(@dep.parent_id)
				full_title = parent.title + ' > ' + @dep.title
			else
				full_title = @dep.title

			# If the department is new, we need to handle updating the UI
			# with the newly saved department once the request comes back with an ID
			if @em.hasById('department', @dep.id)
				model = @em.getById('department', @dep.id)
				model.title = @dep.title
				model._full_title = full_title
				model.user_title = @dep.user_title
				model.parent_id = @dep.parent_id
				@DepartmentData.resetHierarchy()
			else
				promise.success( (result) =>
					@dep.id = result.id

					model = @em.createEntity('department', 'id', @dep.getData())
					model._full_title = full_title

					if not model.parent_id or model.parent_id == "0"
						model.parent_id = null

					@DepartmentData.addToList(model)
					@DepartmentData.resetHierarchy()
					@initDeplistData(@DepartmentData.deps)

					@skipDirtyState()
					if is_new
						@Growl.success("Department was created successfully", =>
							@$state.go('tickets.ticket_deps.edit', {id: @dep.id})
						)
						@$state.go('tickets.ticket_deps.gocreate')
					else
						@$state.go('tickets.ticket_deps')
				)

			@dep.setCheckpoint()
			@depPerms = @getPermsData()

			return promise

		###*
		# Gets property data
		###
		getPropsData: ->
			return @dep.getData()

		propogatePermission: (obj, perm) ->
			if @_propogatePermission_running then return
			@_propogatePermission_running = true
			if obj.type == 'group'
				@agent_perms.setGroupPerm(obj.model.id, perm, '&')
			else
				@agent_perms.setAgentPerm(obj.model.id, perm, '&')
			@_propogatePermission_running = false

		###*
		# Gets permission data that can be posted for saving
		###
		getPermsData: (type = 'all') ->
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

			return perms

	Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()