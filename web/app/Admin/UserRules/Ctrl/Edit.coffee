define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_UserRules_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_UserRules_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams']

		init: ->
			@userRulesData = @DataService.get('UserRules')
			@user_rule = null

		###
 	#
 	###

		initialLoad: ->
			promise = @userRulesData.loadEditUserRuleData(@$stateParams.id || null).then( (data) =>

				@user_rule  = data.user_rule
				@form = data.form
			)
			return promise

		###
		#
 	###

		saveForm: ->

			if not @$scope.form_props.$valid
				return

			is_new = !@user_rule.id

			promise = @userRulesData.saveFormModel(@user_rule, @form)

			@startSpinner('saving')
			promise.then( =>
				@stopSpinner('saving', true).then(=>
					@Growl.success("Saved")
				)

				@skipDirtyState()
				if is_new
					@$state.go('crm.rules.gocreate')
			)

	Admin_UserRules_Ctrl_Edit.EXPORT_CTRL()