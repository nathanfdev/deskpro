define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketDeps_Ctrl_Edit'
		@MODULE_ID = 'Admin_App'
		@DEPS      = ['$scope']

		init: ->

	Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL()