define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketDeps_Ctrl_List'
		@MODULE_ID = 'Admin_App'
		@DEPS      = ['$scope']

		init: ->

	Admin_TicketDeps_Ctrl_List.EXPORT_CTRL()