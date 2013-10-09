define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketAccounts_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketAccounts_Ctrl_List'
		@CTRL_AS = 'TicketAccountsList'
		@DEPS    = ['$rootScope', '$scope', 'Api', 'Growl']
		@CTRL_TYPE = 'list'

		init: ->

		initialLoad: ->
			data_promise = @Api.sendDataGet([
					'/ticket_accounts'
			]).then( (res) =>
				@accounts = res.data.api_ticket_accounts
			)

			return @$q.all([data_promise]);

	Admin_TicketAccounts_Ctrl_List.EXPORT_CTRL()