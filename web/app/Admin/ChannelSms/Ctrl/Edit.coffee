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
		@DEPS    = ['Api', 'Growl', 'SmsAccountsData', '$stateParams']

		init: ->
			@accountId = parseInt(@$stateParams.id || 0)

		getFormModel: ->
			return new Admin_ChannelSms_FormModel_EditSmsAccountModel(@account || {})

		initialLoad: ->
			if @accountId
				promise = @Api.sendGet("/channel/sms/account/#{@accountId}").then((result) =>
					if result.data
						@account = result.data
					@setFormOnScope()
				)
			else
				@setFormOnScope()
			return promise

		setFormOnScope: ->
			@form_model = @getFormModel()
			@$scope.form = @form_model.form

		clearCredentials: ->
			console.log 'ok clear it'
			@form_model.markConnected(false)
			@$scope.connection_problem = false

		connect: ->
			postData = @form_model.getConnectData()
			connectUrl = "/channel/sms/connect_provider"
			promise = @Api.sendPostJson(connectUrl, postData)
			promise.then( (result) =>
				console.log 'from server'
				console.log result
				if result.data.success
					@$scope.connection_problem = false
					@form_model.markConnected(true)
					@form_model.setNumbers(result.data.numbers)
					@form_model.setFriendlyName(result.data.friendly_name)
				else
					@$scope.connection_problem = true
					@form_model.markConnected(false)

				@stopSpinner('sms_connect_provider');
			)
			promise.error( (result) =>
				@$scope.connection_problem = true
				@form_model.markConnected(false)
			)
			@startSpinner('sms_connect_provider');
			return promise

		testRoundTrip: ->
			alert "testing"

		saveAccount: ->
			formData = @form_model.getFormData().account

			if @accountId
				console.log "Save an existing account! POST!"
				@Api.sendPostJson("/channel/sms/account/#{@accountId}", formData).then((result) =>
					console.log result
				)
				# post
			else
				console.log "Put a new SMS Account on the server!"
				console.log formData
				@Api.sendPutJson("/channel/sms/account", formData).then( (result) =>
					console.log result
				)
				# put

			@account = formData


	Admin_ChannelSms_Ctrl_Edit.EXPORT_CTRL()
