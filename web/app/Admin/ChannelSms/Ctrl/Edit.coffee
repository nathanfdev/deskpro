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
		@DEPS    = ['Api', 'Growl', 'SmsAccountsData', '$stateParams', '$state']

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

		connect: ->
			postData = @form_model.getConnectData()
			connectUrl = "/channel/sms/connect_provider"
			promise = @Api.sendPostJson(connectUrl, postData)
			promise.then( (result) =>
				if result.data.success
					@$scope.connection_problem = false
					@form_model.markConnected(true)
					@form_model.setNumbers(result.data.numbers)
					@form_model.setFriendlyName(result.data.friendly_name)
					@ngApply()
				else
					@$scope.connection_problem = true
					@form_model.markConnected(false)
				@stopSpinner('sms_connect_provider')
			)
			promise.error( (result) =>
				@$scope.connection_problem = true
				@form_model.markConnected(false)
				@stopSpinner('sms_connect_provider')
			)
			@startSpinner('sms_connect_provider')
			return promise

		testRoundTrip: ->
			@form_model.markTested(true)
			postData = @form_model.getConnectData()
			connectUrl = "/channel/sms/test_account"
			promise = @Api.sendPostJson(connectUrl, postData)
			promise.then((result) =>
				if result.data.success
					@form_model.markTested(true)
					@form_model.setNumbers(result.data.numbers)
					@form_model.setFriendlyName(result.data.friendly_name)
					@ngApply()
				else
					@form_model.markTested(false)
				@stopSpinner('sms_test')
			)
			promise.error((result) =>
				@$scope.connection_problem = true
				@form_model.markTested(true)
				@stopSpinner('sms_test')
			)
			@startSpinner('sms_test')
			return promise


		saveAccount: ->
			formData = @form_model.getFormData().account
			if @accountId
				@Api.sendPostJson("/channel/sms/account/#{@accountId}", formData).then((result) =>
					@account = formData
					@Growl.success(@getRegisteredMessage('saved_account'))
					@SmsAccountsData.updateModel(@account)
				)
			else
				@Api.sendPutJson("/channel/sms/account", formData).then( (result) =>
					@accountId = result.data.sms_account_id
					@account = formData
					@account.id = @accountId
					@account.phone_number_region = result.data.phone_number_region
					@SmsAccountsData.addToList(@account)
					@Growl.success(@getRegisteredMessage('saved_account'))
					@$state.go('tickets.channel_sms.edit', { id: @accountId })
				)



	Admin_ChannelSms_Ctrl_Edit.EXPORT_CTRL()
