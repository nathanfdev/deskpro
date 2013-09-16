define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_Index extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_Index'
		@DEPS      = ['$scope']

		init: ->

	Admin_Main_Ctrl_Index.EXPORT_CTRL()