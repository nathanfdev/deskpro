define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_UserGroups_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserGroups_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = ['$stateParams']

		init: ->
			@ugData = @DataService.get('UserGroups')
			@user_group = null

		###
 	#
 	###

		initialLoad: ->
			promise = @ugData.loadEditUserGroupData(@$stateParams.id || null).then( (data) =>

				@user_group  = data.user_group
				@form = data.form
			)
			return promise

		###
		#
		###

		saveForm: ->

			if not @$scope.form_props.$valid
				return

			is_new = !@user_group.id

			promise = @ugData.saveFormModel(@user_group, @form)

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