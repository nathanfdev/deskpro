define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditAwaitingAgent extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditAwaitingAgent'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []
		@CTRL_TYPE = 'page'

		init: ->
			return

	Admin_TicketStatuses_Ctrl_EditAwaitingAgent.EXPORT_CTRL()