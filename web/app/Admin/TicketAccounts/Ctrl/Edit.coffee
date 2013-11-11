define [
	'Admin/Main/Ctrl/Base',
	'Admin/TicketAccounts/FormModel/EditTicketAccountModel',
], (
	Admin_Ctrl_Base,
	EditTicketAccountModel
) ->
	class Admin_TicketAccounts_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketAccounts_Ctrl_Edit'
		@CTRL_AS = 'TicketAccountsEdit'
		@DEPS    = ['Api', 'Growl', 'DepartmentData', 'TicketAccountsData', '$stateParams', '$modal']

		init: ->
			@didPassTest = false
			@testMessageCount = 0
			@didConfirmExistingMessages = false
			@test_email = {
				to: window.DP_PERSON_EMAIL,
				from: '',
				subject: 'Test email',
				message: 'This is a test. If you see this email in your inbox, your outgoing email account are correct.'
			}

		initialLoad: ->
			dep_promise = @DepartmentData.loadDepList().then( (departments) =>
				@deps = departments.values()
			)

			if not @$stateParams.id
				@account = {
					email_address: '',
					connection_type: 'pop3',
					in_pop3_account: {},
					in_imap_account: {},
					in_gmial_account: {},
					linked_transport: {
						transport_type: 'smtp',
						transport_options: {}
					}
				}
				@form_model = new EditTicketAccountModel(@account)
				@$scope.form = @form_model.form

				return @$q.all([dep_promise]);
			else
				data_promise = @Api.sendDataGet([
					'/ticket_accounts/' + @$stateParams.id
				]).then( (result) =>
					@account = result.data.api_ticket_accounts_get.ticket_account
					@form_model = new EditTicketAccountModel(@account)
					@$scope.form = @form_model.form
				)

				return @$q.all([dep_promise, data_promise]);


		###
    	# Saves the current form
    	#
    	# @return {promise}
		###
		saveAccount: ->
			postData = @form_model.getFormData()

			@startSpinner('saving_account')
			if @account.id
				is_new = false
				promise = @Api.sendPostJson('/ticket_accounts/' + @account.id, postData)
			else
				is_new = true
				promise = @Api.sendPutJson('/ticket_accounts', postData)

			promise.success( (result) =>
				@account.id = result.id
				@account.is_enabled = true
				@stopSpinner('saving_account', true).then(=>
					@Growl.success(@getRegisteredMessage('saved_account'))
				)

				@form_model.apply()
				@TicketAccountsData.updateModel(@account)

				@skipDirtyState()
				if is_new
					@$state.go('tickets.ticket_accounts.gocreate')
				else
					@$state.go('tickets.ticket_accounts')
			)
			promise.error( (info, code) =>
				@stopSpinner('saving_account', true)
				@applyErrorResponseToView(info)
			)

			return promise


		###
    	# Test current account settings
    	#
    	# @return {promise}
		###
		loadAccountTest: ->
			return @Api.sendPostJson('/ticket_accounts/test-account', @form_model.getFormData()).success( (result) =>
				@didPassTest = result.is_success
			)


		###
    	# Test current outgoing settings with message details from @test_email object.
    	#
    	# @return {promise}
		###
		loadOutgoingAccountTest: ->
			@test_email.from = @form_model.form.email_address

			form_data = @form_model.getFormData().email_transport
			form_data.test_email = @test_email

			return @Api.sendPostJson('/ticket_accounts/test-outgoing-account', form_data)


		###
    	# Show the test account modal
		###
		testAccountModal: ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketAccounts/test-account-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>
					$scope.dismiss = =>
						$modalInstance.dismiss();

					$scope.showLog = =>
						$scope.showing_log = true

					testNow = =>
						$scope.showing_log = false
						$scope.is_testing = true
						@loadAccountTest().success( (result) =>
							$scope.is_testing    = false
							$scope.is_success    = result.is_success
							$scope.log           = result.log
							$scope.message_count = result.message_count
						).error(=>
							$scope.showing_log   = true
							$scope.is_testing    = false
							$scope.is_success    = false
							$scope.log           = "Server Error"
							$scope.message_count = 0
						)

					testNow();

					$scope.testNow = ->
						testNow()
				]
			});


		###
    	# Show the test account modal
		###
		testOutgoingModal: ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketAccounts/test-outgoing-modal.html'),
				resolve: {
					test_email: =>
						@test_email.from = @form_model.form.email_address
						return @test_email
				},
				controller: ['$scope', '$modalInstance', 'test_email', ($scope, $modalInstance, test_email) =>
					$scope.dismiss = =>
						$modalInstance.dismiss();

					$scope.showLog = =>
						$scope.showing_log = true

					console.log(test_email)
					$scope.test_email = test_email

					testNow = =>
						$scope.testing_started = true
						$scope.showing_log = false
						$scope.is_testing = true
						@loadOutgoingAccountTest().success( (result) =>
							$scope.is_testing    = false
							$scope.is_success    = result.is_success
							$scope.log           = result.log
							$scope.message_count = result.message_count
						).error(=>
							$scope.showing_log   = true
							$scope.is_testing    = false
							$scope.is_success    = false
							$scope.log           = "Server Error"
							$scope.message_count = 0
						)

					resetTest = =>
						$scope.testing_started = false

					$scope.testNow = ->
						testNow()
					$scope.resetTest = ->
						resetTest()
				]
			});

	Admin_TicketAccounts_Ctrl_Edit.EXPORT_CTRL()