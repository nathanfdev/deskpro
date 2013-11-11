define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditResolved extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditResolved'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []

		init: ->
			return

	Admin_TicketStatuses_Ctrl_EditResolved.EXPORT_CTRL()