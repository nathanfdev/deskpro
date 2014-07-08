define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_Tasks_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Tasks_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams']


		init: ->



		initialLoad: ->



	Admin_Tasks_Ctrl_Edit.EXPORT_CTRL()