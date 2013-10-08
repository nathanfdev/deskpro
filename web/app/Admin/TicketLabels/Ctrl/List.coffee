define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketLabels_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketLabels_Ctrl_List'
		@CTRL_AS = 'TicketLabelsList'
		@DEPS    = []
		@CTRL_TYPE = 'list'

		init: ->
			@labels = []
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet([
					'/ticket_labels'
			]).then( (res) =>
				for label in res.data.api_ticket_labels.labels
					@labels.push(f)
			)

			return @$q.all([data_promise])


	Admin_TicketLabels_Ctrl_List.EXPORT_CTRL()
