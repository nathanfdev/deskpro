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
			window.DEP_CTRL = this
			@depId = parseInt(@$stateParams.id)
			@depData = @DataService.get('TicketDeps')
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
			promise = @depData.getEditDepartmentData(@depId || null).then( (data) =>
				@dep  = data.dep
				@form = data.form
				@is_custom_layout = @form.use_custom_layout
				@origForm = Util.clone(@form, true)
				@layout_info = data.layout_info

				if @depId
					@layout_info.default = @layout_info.default.filter((x) => return x.id != @depId)
					@layout_info.custom = @layout_info.custom.filter((x) => return x.id != @depId)

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

			# @form is used due to the reason that upon clicking on submit button parent_id still has old value
			if @depData.hasChildrenAndChangedParent(@dep, @form)
				@showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.")
				return

			@startSpinner('saving_dep')

			deferred2 = @$q.defer()

			promise = @depData.saveFormModel(@dep, @form)
			promise.then(=>
				if (@form.use_custom_layout)
					@Api.sendPostJson("/ticket_layouts/#{@dep.id}", {layout: @form.custom_layout}).then(-> deferred2.resolve())
				else
					@Api.sendPostJson("/ticket_layouts/default", {layout: @form.default_layout}).then(-> deferred2.resolve())
					@Api.sendDelete("/ticket_layouts/#{@dep.id}")

				@is_custom_layout = @form.use_custom_layout
			)
			promise.error( (info, code) =>
				@stopSpinner('saving_dep')
				@applyErrorResponseToView(info)
			)

			deferred2.promise.then(=>
				@origForm = Util.clone(@form, true)
				@stopSpinner('saving_dep').then(=>
					@Growl.success(@getRegisteredMessage('saved_dep'), =>
						@$state.go('tickets.ticket_deps.edit', {id: @dep.id})
					)
				)
			)

			return deferred2.promise

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