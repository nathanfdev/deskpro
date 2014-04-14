define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserReg_Ctrl_UsersourceList extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserReg_Ctrl_UsersourceList'
		@CTRL_AS = 'ListCtrl'
		@DEPS = []

		init: ->
			return

		initialLoad: ->
			promise = @Api.sendGet('/apps?tags=usersources').then( (result) =>
				@apps = result.data.apps.filter((x) -> !x.package.is_custom)
			)
			return promise

	Admin_UserReg_Ctrl_UsersourceList.EXPORT_CTRL()