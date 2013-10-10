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
		@CTRL_TYPE = 'page'

		init: ->
			@didPassTest = false
			@testMessageCount = 0
			@didConfirmExistingMessages = false

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

		testAccount: ->
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

		loadAccountTest: ->
			return @Api.sendPostJson('/ticket_accounts/test-account', @form_model.getFormData()).success( (result) =>
				@didPassTest = result.is_success
			)

	Admin_TicketAccounts_Ctrl_Edit.EXPORT_CTRL()