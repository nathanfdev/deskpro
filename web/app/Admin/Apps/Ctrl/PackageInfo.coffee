define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Apps_Ctrl_PackageInfo extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Apps_Ctrl_PackageInfo'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$http']

		init: ->
			@packageName = @$stateParams.name;
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				pack: '/apps/packages/' + @packageName,
			}).then( (result) =>
				@pack = result.data.pack['package']
			)

			return promise

	Admin_Apps_Ctrl_PackageInfo.EXPORT_CTRL()