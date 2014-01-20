define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserGroups_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserGroups_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = ['$stateParams']

		init: ->
			@ugData = @DataService.get('UserGroups')
			@group = null

		initialLoad: ->
			promise = @ugData.loadEditUserGroupData(@$stateParams.id || null).then( (data) =>
				@group     = data.group
				@form      = data.form
				@perm_form = @group.perms
				@perm_form.options = {}

				if @group.id != 1 and data.reg_group.is_enabled
					@perm_form_reg = data.reg_group.perms
				else
					@perm_form_reg = null

				if @perm_form.ticket.reopen_resolved_createnew || @perm_form_reg?.ticket?.reopen_resolved_createnew
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

	Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL()