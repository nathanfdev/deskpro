define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserGroups_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserGroups_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = ['$stateParams']

		init: ->
			@groupId = parseInt(@$stateParams.id) || 0
			@ugData = @DataService.get('UserGroups')
			@group = null

		initialLoad: ->
			promise = @ugData.loadEditUserGroupData(@$stateParams.id || null).then( (data) =>
				@group     = data.group
				@form      = data.form
				@perm_form = @group.perms
				@perm_form.options = {}

				if @group.sys_name == 'everyone'
					@perm_form_everyone = null
					@perm_form_reg      = null
				else if @group.sys_name == 'registered'
					@perm_form_everyone = if data.everyone_group.is_enabled then data.everyone_group.perms else null
					@perm_form_reg      = null
				else
					@perm_form_everyone = if data.everyone_group.is_enabled then data.everyone_group.perms else null
					@perm_form_reg      = if data.reg_group.is_enabled     then data.reg_group.perms       else null

				if @perm_form?.ticket?.reopen_resolved_createnew || @perm_form_reg?.ticket?.reopen_resolved_createnew
					@perm_form.options.reopen_resolved_createnew = 'new_ticket'
				else
					@perm_form.options.reopen_resolved_createnew = 'reject'
			)
			return promise

		saveForm: ->
			if not @$scope.form_props.$valid
				return

			is_new = !@group.id
			promise = @ugData.saveFormModel(@group, @form, @perm_form)

			@startSpinner('saving')
			promise.then( =>
				@stopSpinner('saving', true).then(=>
					@Growl.success("Saved")
				)

				@skipDirtyState()
				if is_new
					@$state.go('crm.groups.gocreate')
			)

		###
    	# Shows the copy settings modal
    	###
		showDelete: ->
			deleteGroup = =>
				p = @ugData.removeGroupById(@groupId)
				p.then(=>
					@$state.go('crm.groups')
				)
				return p

			group = @group
			inst = @$modal.open({
				templateUrl: @getTemplatePath('UserGroups/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.group = group
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doDelete = (options) ->
						$scope.is_loading = true
						deleteGroup().then(-> $modalInstance.dismiss())
				]
			});

	Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL()