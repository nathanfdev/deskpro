define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserReg_Ctrl_UsersourceList extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserReg_Ctrl_UsersourceList'
		@CTRL_AS = 'ListCtrl'
		@DEPS = []

		init: ->
			return

		initialLoad: ->
			promise = @Api.sendGet('/usersources/user').then( (result) =>
				@usersources = result.data.usersources
			)
			return promise

	Admin_UserReg_Ctrl_UsersourceList.EXPORT_CTRL()