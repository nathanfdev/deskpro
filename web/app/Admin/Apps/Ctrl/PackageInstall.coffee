define ['require', 'Admin/Main/Ctrl/Base'], (require, Admin_Ctrl_Base) ->
	class Admin_Apps_Ctrl_PackageInstall extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Apps_Ctrl_PackageInstall'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$http', 'dpTemplateManager']

		init: ->
			@packageName = @$stateParams.name.replace(/\.install$/, '');
			@usersourceType = @$stateParams.usersource_type;
			@$scope.getController = => return this
			@$scope.setPresaveCallback = (callback) => @presaveCallback = callback
			@$scope.enableCustomFooter = => @$scope.has_own_footer = true
			@presaveCallback = null
			return

		initialLoad: ->
			deferred = @$q.defer()

			@Api.sendDataGet({
				pack: '/apps/packages/' + @packageName,
			}).then( (result) =>
				@pack = result.data.pack['package']
				@$scope.pack = @pack

				form_template = @packageName + '/Install/install.html'
				installCtrl = null
				loadingAssets = []
				@$scope.has_display_settings = @pack.settings_def.filter( (x) -> x.type != 'hidden').length > 0

				@$scope.setting_values = { dp_app: { title: @pack.title }}

				for setting in @pack.settings_def
					if setting.default_value
						@$scope.setting_values[setting.name] = setting.default_value

				getResourcePath = (tag, name) =>
					asset = @pack.assets.filter((x) -> x.tag == tag && x.name == name)[0]
					return if asset then asset.blob.relative_url else null

				if path = getResourcePath('html', 'AdminInterface/Install/install.html')
					loadingAssets.push(@$http.get(path, { responseType: "text"}).success((data) =>
						@dpTemplateManager.setTemplate(form_template, data)
					))
				if path = getResourcePath('js', 'AdminInterface/Install/install.js')
					jsDeferred = @$q.defer()
					require([path], (c) =>
						installCtrl = c
						jsDeferred.resolve()
					)
					loadingAssets.push(jsDeferred.promise)

				if loadingAssets.length
					@$q.all(loadingAssets).then(=>
						if installCtrl
							@$scope.install_ctrl = installCtrl
						else
							@$scope.install_ctrl = [=>
								return
							]

						if form_template
							@$scope.form_template = form_template
							@$scope.default_form = false
						else
							@$scope.default_form = true

						deferred.resolve()
					)
				else
					@$scope.default_form = true
					deferred.resolve()

			)

			return deferred.promise

		installApp: ->
			@startSpinner('saving_settings')
			if @presaveCallback
				@presaveCallback(@$scope.setting_values).then( =>
					@doInstall().catch(=>
						@stopSpinner('saving_settings', true)
					)
				, =>
					@stopSpinner('saving_settings', true)
				)
			else
				@doInstall().catch(=>
					@stopSpinner('saving_settings', true)
				)

		cancelInstall: ->
			if @usersourceType == 'user'
				@$state.go('crm.usersources')
			else if @usersourceType == 'agent'
				@$state.go('crm.usersources')
			else
				@$state.go('apps.apps.package', {name: @pack.name});

		doInstall: ->
			listCtrl = null
			if @$scope.$parent.ListCtrl?.addAppInstance?
				listCtrl = @$scope.$parent.ListCtrl

			setting_values = @$scope.setting_values

			url = "/apps/packages/#{@packageName}"
			if @usersourceType then url += '?usersource_type=' + @usersourceType
			return @Api.sendPutJson(url, {settings: setting_values}).success( (info) =>
				if listCtrl
					instanceInfo = {
						id: info.id,
						title: setting_values.dp_app.title,
						package_name: @pack.name,
						package: @pack
					}
					listCtrl.addAppInstance(instanceInfo)

				if @usersourceType == 'user'
					@$scope.$parent?.ListCtrl?.refresh()
					@$state.go('crm.usersources.id', {id: info.id})
				else if @usersourceType == 'agent'
					@$scope.$parent?.ListCtrl?.refresh()
					@$state.go('crm.usersources.id', {id: info.id})
				else
					@$state.go('apps.apps.instance', {id: info.id});
			);

	Admin_Apps_Ctrl_PackageInstall.EXPORT_CTRL()