define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketLabels_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketLabels_Ctrl_List'
		@CTRL_AS = 'TicketLabelsList'
		@DEPS    = []
		@CTRL_TYPE = 'list'

		init: ->
			@labels = []
			@delete_mode = false
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet([
					'/ticket_labels'
			]).then( (res) =>
				console.log res.data
#				for label in res.data.api_ticket_labels.labels
#					@labels.push(label)
			)

			return @$q.all([data_promise])
			
		startDelete: (label) ->
			@delete_mode = true
			@Api.sendDelete('/ticket_labels/',{
				label:label.label
			}).success( =>
				@labels.remove(label)
			).always(
				@delete_mode = false;
			)


	Admin_TicketLabels_Ctrl_List.EXPORT_CTRL()