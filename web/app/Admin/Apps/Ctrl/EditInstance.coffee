define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Apps_Ctrl_EditInstance extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Apps_Ctrl_EditInstance'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			return null

	Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL()