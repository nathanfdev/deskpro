define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_Usersources_Ctrl_New extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Usersources_Ctrl_New'
		@CTRL_AS = 'NewCtrl'
		@DEPS = ['$stateParams']

		init: ->
			@usersourceType = @$stateParams.usersource_type

		initialLoad: ->
			url = '/usersources/available/app-packages/' + @usersourceType
			@Api.sendGet(url).then((res) =>
				@packages = res.data
			)

	Admin_Usersources_Ctrl_New.EXPORT_CTRL()