define [
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Model/DepAgentPermMatrix',
	'DeskPRO/Util/Util'
], (
	Admin_Ctrl_Base,
	Admin_Main_Model_DepAgentPermMatrix,
	Util
) ->
	class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketDeps_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$templateCache']

		init: ->
			@depData = @DataService.get('TicketDeps')
			@$scope.is_custom_layout = false
			@$scope.defaultLayout = {}
			@$scope.customLayout = {}

			@$scope.$watch('EditCtrl.form.parent_id', (newVal) =>
				newVal = parseInt(newVal)
				if not newVal
					@$scope.show_parent_warning = false
					return

				parent = @depData.findListModelById(newVal)
				if parent and not parent.children.length
					@$scope.show_parent_warning = parent
				else
					@$scope.show_parent_warning = false
			)

			@$scope.embed_code_type = 'department'

			@$scope.embedEditorLoaded = (editor) ->
				$(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor')

		resetForm: ->
			@form = Util.clone(@origForm, true)

		initialLoad: ->
			promise = @depData.getEditDepartmentData(@$stateParams.id || null).then( (data) =>
				@dep  = data.dep
				@form = data.form
				@origForm = Util.clone(@form, true)

				@usergroups  = data.usergroups
				@agentgroups = data.agentgroups
				@agents      = data.agents

				@email_accounts  = data.email_accounts
				@dep_parent_list = data.dep_parent_list

				for name in ['link', 'win', 'embed', 'phpapi']
					tpl = @getTemplatePath("TicketDeps/code-"+name+".html")
					code = @$templateCache.get(tpl).replace(/%DEPID%/g, @dep.id)
					code_all = @$templateCache.get(tpl).replace(/%DEPID%/g, 0)
					@$scope['code_' + name] = code
					@$scope['code_all_' + name] = code_all
			)

			return promise

		isDirtyState: ->
			return not Util.equals(@form, @origForm)

		###*
		# Save everything
		###
		saveAll: ->
			if not @$scope.form_props.$valid
				return

			@startSpinner('saving_dep')

			promise = @depData.saveFormModel(@dep, @form)

			promise.then(=>
				@origForm = Util.clone(@form, true)
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

			return promise

		propogatePermission: (obj, perm) ->
			if @_propogatePermission_running then return
			@_propogatePermission_running = true
			if obj.type == 'group'
				@form.agent_perms.setGroupPerm(obj.model.id, perm, '&')
			else
				@form.agent_perms.setAgentPerm(obj.model.id, perm, '&')
			@_propogatePermission_running = false

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