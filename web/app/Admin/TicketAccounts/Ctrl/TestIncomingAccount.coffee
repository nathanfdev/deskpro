define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_TicketAccounts_Ctrl_TestIncomingAccount extends Admin_Ctrl_Base
		@CTRL_AS   = null
		@CTRL_ID   = 'Admin_TicketAccounts_Ctrl_TestIncomingAccount'
		@CTRL_TYPE = 'modal'
		@DEPS      = ['$modalInstance', 'account_form']

		init: ->
			@$scope.dismiss = =>
				@$modalInstance.dismiss();

			@$scope.showLog = =>
				@$scope.showing_log = true

			@$scope.is_testing = true
			@$scope.testNow = =>
				@testNow()

			@testNow()

		testNow: ->
			@$scope.showing_log = false
			@$scope.is_testing = true
			@Api.sendPostJson('/ticket_accounts/test-account', @account_form).success( (result) =>
				@$scope.is_testing = false

				@$scope.is_success    = result.is_success
				@$scope.log           = result.log
				@$scope.message_count = result.message_count
			).error(=>
				@$scope.showing_log = true
				@$scope.is_testing = false

				@$scope.is_success    = false
				@$scope.log           = "Server Error"
				@$scope.message_count = 0
			)

	Admin_TicketAccounts_Ctrl_TestIncomingAccount.EXPORT_CTRL()