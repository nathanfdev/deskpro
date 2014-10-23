define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_UserRules_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_UserRules_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams', 'Api']

		init: ->
			@userRulesData = @DataService.get('UserRules')
			@user_rule = null
			@apply_log = ''
			@apply_started = false

		initialLoad: ->
			promise = @userRulesData.loadEditUserRuleData(@$stateParams.id || null).then( (data) =>
				@user_rule  = data.user_rule
				@form = data.form
			)
			return promise

		saveForm: ->

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

		###
 		# Applying current user rule to all users
		###
		applyRuleToUsers: ->
			page = -1
			@apply_started = true

			doRequest = =>
				page++
				@Api.sendGet('/user_rules_apply/' + @user_rule.id + '/page_' + page).success( (result) =>
					if !result.completed and result.success
						@apply_log += 'Done batch #' + (page + 1) + ' ...<br>'
						doRequest()
					else
						@apply_log += 'Completed<br>'
				).error( =>
					@apply_log = 'Error occurred<br>'
				)

			doRequest()

	Admin_UserRules_Ctrl_Edit.EXPORT_CTRL()