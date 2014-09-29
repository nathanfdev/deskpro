define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/Usersources/Helper/UsersourceTypeDecider']
, (Admin_Ctrl_Base, Util, Admin_Usersources_Helper_UsersourceTypeDecider) ->
	class Admin_Usersources_Ctrl_EditInstance extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Usersources_Ctrl_EditInstance'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$http', 'dpTemplateManager']

		init: ->
			@instanceId = @$stateParams.id
			@permission_groups = [];
			@$scope.getController = => return this
			@$scope.setPresaveCallback = (callback) => @presaveCallback = callback
			@$scope.enableCustomFooter = => @$scope.has_own_footer = true
			@usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state);
			@presaveCallback = null
			@app = null
			return

		initialLoad: ->
			d = @$q.defer()
			d2 = @$q.defer()

			@Api.sendDataGet({
				app: '/apps/instances/' + @instanceId
			}).then( (result) =>
				@app = result.data.app?.app;

				if @app
					@Api.sendDataGet({
						pack: '/apps/packages/' + @app.package_name
					}).then( (result) =>
						@pack = result.data.pack['package']
						@packageName = @pack.name
						d.resolve()
					)
				else
					@usersourceId = @instanceId
					@Api.sendGet('/usersources/' + @usersourceType + '/' + @usersourceId).then((result) =>
						@usersource = result.data.usersource
						d.resolve()
					)
			)

			d.promise.then( =>
				# we do nothing here if its a direct usersource, but if its an app we have some work t do
				if not @app
					d2.resolve()
				else
					# this is an app instance

					if @permission_groups.length == 0
						@Api.sendGet('/agent_groups').then( (res) =>
							res.data.groups.forEach( (val) =>
								@permission_groups.push({"value": val.id.toString(), "label": val.title})
							)
						)

					@$scope.pack = @pack
					@$scope.setting_values = @app.settings
					if not @$scope.setting_values || Util.isArray(@$scope.setting_values)
						@$scope.setting_values = {}
					@$scope.setting_values.dp_app = { title: @app.title }
					@$scope.usersource_details = { metadata_url: 'http://google.com/meta.xml', consumer_url: 'consumer service url here', metadata_text: 'this will be metadata' }

					@$scope.has_display_settings = @pack.settings_def.filter( (x) -> x.type != 'hidden').length > 0
					form_template = @packageName + '/AdminInterface/Install/settings.html'
					installCtrl = null
					loadingAssets = []

					getResourcePath = (tag, name) =>
						asset = @pack.assets.filter((x) -> x.tag == tag && x.name == name)[0]
						if asset
							cachebust = window.DP_BUILD_TIME
							asset.blob.relative_url + '?' + cachebust
						else
							null

					if path = getResourcePath('html', 'AdminInterface/Install/settings.html')
						loadingAssets.push(@$http.get(path, { responseType: "text"}).success((data) =>
							@dpTemplateManager.setTemplate(form_template, data)
						))
					if path = getResourcePath('js', 'AdminInterface/Install/settings.js')
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

							d2.resolve()
						)
					else
						@$scope.default_form = true
						d2.resolve()
			)
			return d2.promise

		saveSettings: ->
			@startSpinner('saving_settings')
			if @presaveCallback
				@presaveCallback().then( =>
					@doSaveSettings().catch(=>
						@stopSpinner('saving_settings', true)
					)
				, =>
					@stopSpinner('saving_settings', true)
				)
			else
				@doSaveSettings().finally(=>
					@stopSpinner('saving_settings', true)
				)

		doSaveSettings: ->
			postData = {
				settings: @$scope.setting_values
			}

			@Api.sendPostJson("/apps/instances/#{@instanceId}", postData).then(=>
				@stopSpinner('saving_settings').then(=>
					@$scope.$parent?.ListCtrl?.refresh()
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			)

		saveUsersource: ->
			@startSpinner('saving_settings')

			postData = {
				title: @usersource.title,
				is_enabled: @usersource.is_enabled
			}

			@Api.sendPostJson('/usersources/' + @usersourceType + '/' + @usersourceId, postData).then(=>
				@stopSpinner('saving_settings').then(=>
					@$scope.$parent?.ListCtrl?.refresh()
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			)

		cannotDeleteUsersource: ->
			alert "The DeskPRO usersource cannot be uninstalled. However, you can disable it by unchecking the box on the form and saving."


		###
    	# Shows readme modal window
    	###
		showReadme: ->
			@$modal.open({
				templateUrl: @getTemplatePath('Apps/readme-modal.html'),
				controller: ['$scope', '$modalInstance', 'pack', ($scope, $modalInstance, pack) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.pack = pack
				],
				resolve: {
					pack: =>
						return @pack
				}
			})


		###
		# SHow delete modal
		###
		startDelete: ->
			doDelete = =>
				@Api.sendDelete('/apps/instances/' + @app.id).success( =>

					# If we are viewing with the parent list, we need to remove this
					# app from the list
					if @$scope.$parent?.ListCtrl?
						@$scope.$parent?.ListCtrl?.refresh()

					# close this view
					@$state.go('^')
				)

			@$modal.open({
				templateUrl: @getTemplatePath('Apps/instance-delete-modal.html'),
				controller: ['app', '$scope', '$modalInstance', (app, $scope, $modalInstance) ->
					$scope.app = app
					$scope.dismiss = ->
						$modalInstance.close();

					$scope.confirm = ->
						$scope.is_loading = true
						doDelete().then(->
							$modalInstance.close();
						)
				],
				resolve: {
					app: =>
						return @app
				}
			});

	Admin_Usersources_Ctrl_EditInstance.EXPORT_CTRL()