define [
	'Admin/Main/Ctrl/Base',
	'Admin/Apps/Ctrl/EditInstance'
], (
	Admin_Ctrl_Base, Admin_Apps_Ctrl_EditInstance
) ->
	class Admin_Usersources_Ctrl_Edit extends Admin_Apps_Ctrl_EditInstance
		@CTRL_ID = 'Admin_Usersources_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS = ['$stateParams']

		init: ->
			@usersourcesDataService = @DataService.get('Usersources')
			@usersourceId = @$stateParams.id

		initialLoad: ->
			promise = @Api.sendGet('/usersources/user/' + @usersourceId).then((result) =>
				@usersource = result.data.usersource
				@app = result.data.app
				console.log @app
				console.log @usersource
			)
			return promise

	Admin_Usersources_Ctrl_Edit.EXPORT_CTRL()