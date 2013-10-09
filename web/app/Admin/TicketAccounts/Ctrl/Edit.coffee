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

		initialLoad: ->
			dep_promise = @DepartmentData.loadDepList().then( (departments) =>
				@deps = departments.values()
			)

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
				controller: 'Admin_TicketAccounts_Ctrl_TestIncomingAccount',
				resolve: {
					account_form: =>
						return @form_model.getFormData()
				}
			});

	Admin_TicketAccounts_Ctrl_Edit.EXPORT_CTRL()