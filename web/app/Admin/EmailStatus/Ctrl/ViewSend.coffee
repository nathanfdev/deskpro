define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
	class Admin_EmailStatus_Ctrl_ViewSend extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSend'
		@CTRL_AS = 'List'
		@DEPS    = []

		init: ->
			return

	Admin_EmailStatus_Ctrl_ViewSend.EXPORT_CTRL()