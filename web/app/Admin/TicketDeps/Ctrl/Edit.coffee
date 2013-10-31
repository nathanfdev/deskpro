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

			@$scope.is_custom_layout = false
			@$scope.defaultLayout = {}
			@$scope.customLayout = {}

			@$scope.$watch('TicketDepsEdit.dep.parent_id', (newVal) =>
				newVal = parseInt(newVal)
				if not newVal
					@$scope.show_parent_warning = false
					return

				parent = @DepartmentData.deps.get(newVal)
				if parent and not parent._child_ids?.length
					@$scope.show_parent_warning = parent
				else
					@$scope.show_parent_warning = false
			)

			@trigger = {
				user_send_newuserticket_response: {
					enabled: false,
					template_name: "",
					from_name: 'department_title',
					from_name_custom: ''
				},
				user_send_newagentreply_response: {
					enabled: false,
					template_name: "",
					from_name: 'performer',
					from_name_custom: ''
				},
				user_send_newuserreply_response: {
					enabled: false,
					template_name: "",
					from_name: 'department_title',
					from_name_custom: ''
				}
			}

			@$scope.embed_code_type = 'department'

			@$scope.embedEditorLoaded = (editor) ->
				$(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor')

		resetForm: ->
			@restoreState()
			@saveState('dep', 'agent_perms', 'usergroups')

		initialLoad: ->
			return @loadDepartment()

		checkDirtyState: ->
			if not @dep?.id then return false

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
						'/ticket_accounts',
					])
				else
					@Api.sendDataGet([
						'/agents',
						'/agentgroups',
						'/usergroups',
						'/ticket_accounts',
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
				if not dep.email_gateway_id then dep.email_gateway_id = 0
				@dep = @em.createUnmanagedEntity('department', 'id', dep)

				@dep._enable_user_title = !!@dep.user_title

				@initDeplistData(departments)
				@initEmailAccountsData(data_results.data.api_ticket_accounts.ticket_accounts)
				@initData(
					{ usergroups: dep_data.perms_usergroup_ids, agentgroups: dep_data.perms_agentgroup_ids, agents: dep_data.perms_agent_ids },
					data_results.data.api_agents_list.agents,
					data_results.data.api_agentgroups_list.agentgroups,
					data_results.data.api_usergroups_list.usergroups
				)

				if @dep.email_gateway_id == 0 and @email_accounts.length
					@dep.email_gateway_id = _.first(@email_accounts).id

				@saveState('dep', 'agent_perms', 'usergroups')

				if @dep.id
					@resolveWaitEntityPromise()
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

			matrix.initPerms(department_perms.agentgroups, department_perms.agents)
			@agent_perms = matrix

			if department_perms.usergroups
				ugroup_map = {}
				for p in department_perms.usergroups
					ugroup_map[p.usergroup_id] = p

				for ugroup in usergroups
					if ugroup_map[ugroup.id]
						ugroup.use = true

			@usergroups = usergroups

			for name in ['link', 'win', 'embed', 'phpapi']
				tpl = @getTemplatePath("TicketDeps/code-"+name+".html")
				code = @$templateCache.get(tpl).replace(/%DEPID%/g, @dep.id)
				code_all = @$templateCache.get(tpl).replace(/%DEPID%/g, 0)
				@$scope['code_' + name] = code
				@$scope['code_all_' + name] = code_all

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


		initEmailAccountsData: (accounts) ->
			@email_accounts = accounts

		###*
		# Save everything
		###
		saveAll: ->
			if not @$scope.form_props.$valid
				return

			if not @dep._enable_user_title
				@dep.user_title = ''

			props = @getPropsData()
			props.move_tickets_to = 'self'

			postData = {
				department: props,
				permissions: @getPermsData()
			}

			@startSpinner('saving_dep')

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

			promise.then(=>
				@stopSpinner('saving_dep').then(=>
					@Growl.success(@getRegisteredMessage('saved_dep'), =>
						@$state.go('tickets.ticket_deps.edit', {id: @dep.id})
					)
				)
			)
			promise.error( (info, code) =>
				@stopSpinner('saving_dep')
				@applyErrorResponseToView(info)
			)

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
						if @dep.id
							@resolveWaitEntityPromise()

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
			data = @dep.getData()
			data.email_gateway = @dep.email_gateway_id
			data.parent = @dep.parent_id
			return data

		propogatePermission: (obj, perm) ->
			if @_propogatePermission_running then return
			@_propogatePermission_running = true
			if obj.type == 'group'
				@agent_perms.setGroupPerm(obj.model.id, perm, '&')
			else
				@agent_perms.setAgentPerm(obj.model.id, perm, '&')
			@_propogatePermission_running = false

		###
		# Gets permission data that can be posted for saving
		###
		getPermsData: (type = 'all') ->
			perms = []

			if type == 'all' || type == 'agents'
				for agentObj in @agent_perms.agents
					if agentObj.perms.full.state
						perms.push({
							person_id: agentObj.model.id,
							name: 'full',
							value: 1
						})
					else if agentObj.perms.assign.state
						perms.push({
							person_id: agentObj.model.id,
							name: 'assign',
							value: 1
						})

			if type == 'all' || type == 'agentgroups'
				for groupObj in @agent_perms.groups
					if groupObj.perms.full.state
						perms.push({
							usergroup_id: groupObj.model.id,
							name: 'full',
							value: 1
						})
					else if groupObj.perms.assign.state
						perms.push({
							usergroup_id: groupObj.model.id,
							name: 'assign',
							value: 1
						})

			if type == 'all' || type == 'usergroups'
				for usergroup in @usergroups
					if usergroup.use
						perms.push({
							usergroup_id: usergroup.id,
							name: 'use',
							value: 1
						})

			return perms

		###
		# Open the email editor
		###
		showEmailEditor: (template_name, custom_name) ->
			modalInstance = @$modal.open({
				templateUrl: DP_BASE_ADMIN_URL+'/load-view/Templates/modal-email-editor.html',
				controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
				resolve: {
					templateName: ->
						return custom_name

					variantOf: ->
						return template_name
				}
			})

			return modalInstance

	Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()