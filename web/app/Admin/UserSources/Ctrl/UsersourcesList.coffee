define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Usersources_Ctrl_UsersourcesList extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Usersources_Ctrl_UsersourcesList'
		@CTRL_AS = 'ListCtrl'
		@DEPS = []

		init: ->
			return

		initialLoad: ->
			promise = @Api.sendGet('/usersources/user').then( (result) =>
				@usersources = result.data.usersources
			)
			return promise

	Admin_Usersources_Ctrl_UsersourcesList.EXPORT_CTRL()