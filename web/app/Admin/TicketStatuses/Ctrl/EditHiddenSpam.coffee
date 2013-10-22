define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditHiddenSpam extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenSpam'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []
		@CTRL_TYPE = 'page'

		init: ->
			@auto_purge_time = 604800
			return

		initialLoad: ->
			promise = @Api.sendGet("/ticket_statuses/spam").success( (data) =>
				@auto_purge_time = data.spam_info.auto_purge_time
			);

			return promise

	Admin_TicketStatuses_Ctrl_EditHiddenSpam.EXPORT_CTRL()