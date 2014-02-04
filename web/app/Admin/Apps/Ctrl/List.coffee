define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Apps_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Apps_Ctrl_List'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->
			@$scope.hide_installed = true;
			@$scope.packagesFilter = (hide_installed) ->
				is_installed = !hide_installed
				return (itm) ->
					return !itm.is_installed || itm.is_installed == is_installed

			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				apps: '/apps',
			}).then( (result) =>
				@packages = result.data.apps.packages
				@apps     = result.data.apps.apps
			)
			return promise

		addAppInstance: (instanceInfo) ->
			if not @apps then @apps = []
			@apps.push(instanceInfo)

			for p in @packages
				if p.name == instanceInfo.package.name
					if not p.apps then p.apps = []
					p.apps.push(instanceInfo)
					p.is_installed = true
					break

	Admin_Apps_Ctrl_List.EXPORT_CTRL()