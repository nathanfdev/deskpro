define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Agents_Ctrl_Import extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_Import'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			@$scope.busy = false


		initialLoad: ->




	Admin_Agents_Ctrl_Import.EXPORT_CTRL()