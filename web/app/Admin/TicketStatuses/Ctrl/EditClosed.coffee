define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditClosed extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditClosed'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []
		@CTRL_TYPE = 'page'

		init: ->
			@auto_purge_time = 604800
			return

		initialLoad: ->
			promise = @Api.sendGet("/ticket_statuses/closed").success( (data) =>
				@enabled = data.closed_info.enabled
				@auto_archive_time = data.closed_info.auto_archive_time
			);

			return promise

	Admin_TicketStatuses_Ctrl_EditClosed.EXPORT_CTRL()