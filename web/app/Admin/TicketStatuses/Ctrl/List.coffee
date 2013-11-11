define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_List'
		@CTRL_AS = 'TicketStatusesList'
		@DEPS = []

		init: ->
			return

	Admin_TicketStatuses_Ctrl_List.EXPORT_CTRL()