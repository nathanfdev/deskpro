define [
	'Admin/Main/Ctrl/Base',
	'Admin/Apps/Ctrl/EditInstance',
	'Admin/Usersources/Helper/UsersourceTypeDecider'
], (
	Admin_Ctrl_Base, Admin_Apps_Ctrl_EditInstance, Admin_Usersources_Helper_UsersourceTypeDecider
) ->
	class Admin_Usersources_Ctrl_Edit extends Admin_Apps_Ctrl_EditInstance
		@CTRL_ID = 'Admin_Usersources_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS = ['$stateParams']

		init: ->
			@usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state);
			@usersourcesDataService = @DataService.get('Usersources')
			@usersourceId = @$stateParams.id

		initialLoad: ->
			promise = @Api.sendGet('/usersources/' + @usersourceType + '/' + @usersourceId).then((result) =>
				@usersource = result.data.usersource
				console.log @usersource
			)
			return promise

	Admin_Usersources_Ctrl_Edit.EXPORT_CTRL()