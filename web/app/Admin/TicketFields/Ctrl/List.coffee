define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_TicketFields_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_List'
		@CTRL_AS = 'TicketFieldsList'
		@DEPS    = []

		init: ->
			@ticket_fields = @DataService.get('TicketFields')
			@custom_fields = []
			@field_enabled = {}
			return

		initialLoad: ->
			promise = @ticket_fields.loadList()
			promise.then( (list) =>
				@custom_fields = list
				@field_enabled = @ticket_fields.field_enabled
			)

			return promise

	Admin_TicketFields_Ctrl_List.EXPORT_CTRL()