define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketFields_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_List'
		@CTRL_AS = 'TicketFieldsList'
		@DEPS    = []
		@CTRL_TYPE = 'list'

		init: ->
			@custom_fields = []
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet([
					'/ticket_fields'
			]).then( (res) =>
				for f in res.data.api_ticket_fields.custom_fields
					@custom_fields.push(f)
			)

			return @$q.all([data_promise])


	Admin_TicketFields_Ctrl_List.EXPORT_CTRL()