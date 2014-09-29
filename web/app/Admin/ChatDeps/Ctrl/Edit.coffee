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
		@DEPS      = ['$upload', '$http']

		###
 	#
 	###

		init: ->

			@depData = @DataService.get('ChatDeps')
			@$scope.icon_image = null
			@$scope.$on 'icon.selected', (e, path) => @selectIcon path

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
				@setAvatar @dep.avatar
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

			is_new = !@dep.id

			if not @$scope.form_props.$valid
				return

			# @form is used due to the reason that upon clicking on submit button parent_id still has old value

			if @depData.hasChildrenAndChangedParent(@dep, @form)
				@showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.")
				return

			@startSpinner('saving_dep')

			if @form.enable_avatar
				@form.avatar = @dep.avatar?.id || null
			else
				@form.avatar = null
			promise = @depData.saveFormModel(@dep, @form)

			promise.success( =>

				@origForm = Util.clone(@form, true)

				@stopSpinner('saving_dep', true).then( =>
					@Growl.success(@getRegisteredMessage('saved_dep'))
				)

				if is_new
					@$state.go('chat.chat_deps.gocreate')
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

				if parent and parent.children and not parent.children.length
					@$scope.show_parent_warning = parent
				else
					@$scope.show_parent_warning = false
			)

		###
 	#
		###

		propogatePermission: (obj, perm) ->

			if @_propogatePermission_running then return

			@_propogatePermission_running = true
			if obj.type == 'group'
				@form.agent_perms.setGroupPerm(obj.model.id, perm, '&')
			else
				@form.agent_perms.setAgentPerm(obj.model.id, perm, '&')

			@_propogatePermission_running = false



		setAvatar: (blob) =>
			@dep.avatar = blob
			if !blob?
				@$scope.icon_image = null
				@form.enable_avatar = false
			else
				@$scope.icon_image = blob.thumbnail_url_50
				@form.enable_avatar = true



		onFileSelect: (files) ->
			@$scope.uploading = false
			file = files[0]

			@$upload.upload({
				url: @$http.formatApiUrl('/misc/upload'),
				data: { is_image: true },
				file: file
			}).success( (data) =>
				@$scope.uploading = false
				@setAvatar data.blob
			).error( (data) =>
				@$scope.uploading = false
				@Growl.error data?.error_message || 'Error'
			)



		selectIcon: (image) =>
			setAvatar null if !image?

			@$scope.uploading = true
			@Api.sendPostJson('/misc/upload', {path: image, is_image: true}).then(
				(data) =>
					@$scope.uploading = false
					@setAvatar data.data.blob
				() =>
					@$scope.uploading = false
			)


	Admin_ChatDeps_Ctrl_Edit.EXPORT_CTRL()