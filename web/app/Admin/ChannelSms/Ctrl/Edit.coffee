define [
	'Admin/Main/Ctrl/Base',
	'Admin/ChannelSms/FormModel/EditSmsAccountModel'
], (
	Admin_Ctrl_Base,
	Admin_ChannelSms_FormModel_EditSmsAccountModel
) ->
	class Admin_ChannelSms_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ChannelSms_Ctrl_Edit'
		@CTRL_AS = 'ChannelSmsEdit'
		@DEPS    = ['Api', 'Growl', 'SmsAccountsData', '$stateParams', '$state', '$timeout']

		init: ->
			@accountId = parseInt(@$stateParams.id || 0)

		getFormModel: ->
			return new Admin_ChannelSms_FormModel_EditSmsAccountModel(@account || {})

		initialLoad: ->
			if @accountId
				promise = @Api.sendGet("/channel/sms/account/#{@accountId}").then((result) =>
					if result.data
						@account = result.data
						@form_model = @getFormModel()
					@setFormOnScope()
				)
			else
				@setFormOnScope()
			return promise

		setFormOnScope: ->
			if not @form_model
				@form_model = @getFormModel()
			@$scope.form = @form_model.form

		clearCredentials: ->
			@form_model.markConnected(false)
			@$scope.connection_problem = false

		getPostData: ->
			return {account: @form_model.getFormData()}

		connect: ->
			postData = @getPostData()
			promise = @Api.sendPostJson("/channel/sms/connect_provider", postData)
			promise.then( (result) =>
				if result.data.success
					@$scope.connection_problem = false
					@account = result.data.account
					@form_model.setAccountData(result.data.account)
					if @accountId
						@SmsAccountsData.updateModel(@account)
					@ngApply()
					@Growl.success(@getRegisteredMessage('connected'))
				else
					@$scope.connection_problem = true
					@form_model.markConnected(false)
					@Growl.error(@getRegisteredMessage('connected_fail'))
				@stopSpinner('sms_connect_provider')
			)
			promise.error( (result) =>
				@$scope.connection_problem = true
				@form_model.markConnected(false)
				@stopSpinner('sms_connect_provider')
				@Growl.error(@getRegisteredMessage('connected_fail'))
			)
			@startSpinner('sms_connect_provider')
			return promise

		setupAndTest: ->
			postData = @getPostData()
			promise = @Api.sendPostJson("/channel/sms/setup-and-test/twilio", postData)
			promise.then((result) =>
				if result
					checkTestStatus = =>
						url = "/channel/sms/account/#{@accountId}"
						@$timeout =>
							@Api.sendGet(url).then((result) =>
								console.log(result)
								if result.data.is_tested
									@stopSpinner('sms_test_provider')
									@account = result.data
									@form_model.setAccountData(result.data)
									@SmsAccountsData.updateModel(@account)
									@ngApply()
									@Growl.success(@getRegisteredMessage('setup_and_tested_success'))
								else
									checkTestStatus()
							)
						, 1000
					checkTestStatus()
				else
					@Growl.error(@getRegisteredMessage('connected_fail'))
					@form_model.markTested(false)
			)
			promise.error((result) =>
				@$scope.connection_problem = true
				@Growl.error(@getRegisteredMessage('connected_fail'))
				@stopSpinner('sms_test_provider')
			)
			@startSpinner('sms_test_provider')
			return promise

		saveAccount: ->
			postData = @getPostData()
			if @accountId
				@Api.sendPostJson("/channel/sms/account/#{@accountId}", postData).then((result) =>
					@account = result.data.account
					@SmsAccountsData.updateModel(@account)
					@Growl.success(@getRegisteredMessage('saved_account'))
				)
			else
				@Api.sendPutJson("/channel/sms/account", postData).then( (result) =>
					@account = result.data.account
					@accountId = @account.id
					@SmsAccountsData.addToList(@account)
					@Growl.success(@getRegisteredMessage('saved_account'))
					@$state.go('tickets.channel_sms.edit', { id: @accountId })
				)



	Admin_ChannelSms_Ctrl_Edit.EXPORT_CTRL()
