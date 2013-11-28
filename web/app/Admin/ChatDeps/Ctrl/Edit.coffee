define [
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Model/DepAgentPermMatrix',
	'DeskPRO/Util/Util'
], (
	Admin_Ctrl_Base,
	Admin_Main_Model_DepAgentPermMatrix,
	Util
) ->
	class Admin_ChatDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_ChatDeps_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = []

		###
 	#
 	###

		init: ->

			@depData = @DataService.get('ChatDeps')

			@initializeScopeWatching()

		###
		#
 	###

		resetForm: ->

			@form = Util.clone(@origForm, true)

		###
 	# Loads data that will be used inside the view into scope of controller
 	###

		initialLoad: ->

			promise = @depData.getEditDepartmentData(@$stateParams.id || null).then( (data) =>

				@dep  = data.dep
				@form = data.form
				@origForm = Util.clone(@form, true)

				@usergroups  = data.usergroups
				@agentgroups = data.agentgroups
				@agents      = data.agents

				@dep_parent_list = data.dep_parent_list
			)

			return promise

		###
 	# Checks whether data entered to form was changed or not
  # This is useful for showing modal dialog that notifies user that he has edited the form
		###

		isDirtyState: ->
			return not Util.equals(@form, @origForm)

		###
		# Saves the form
		###

		saveAll: ->

			if not @$scope.form_props.$valid
				return

			@startSpinner('saving_dep')

			promise = @depData.saveFormModel(@dep, @form)

			promise.success( =>

				@origForm = Util.clone(@form, true)

				@stopSpinner('saving_dep').then( =>
					@Growl.success(@getRegisteredMessage('saved_dep'), =>
						@$state.go('chat.chat_deps.edit', {id: @dep.id})
					)
				)
			)

			promise.error( (info, code) =>

				@stopSpinner('saving_dep')
				@applyErrorResponseToView(info)
			)

			return promise

		###
 	# Watches for changing of parent_id that is in scope
  # This is usable inside view to show some notification information
		###

		initializeScopeWatching: ->

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


	Admin_ChatDeps_Ctrl_Edit.EXPORT_CTRL()