define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], (Admin_Ctrl_Base, Util) ->
	class Admin_Apps_Ctrl_EditInstance extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Apps_Ctrl_EditInstance'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			@instanceId = parseInt(@$stateParams.id)
			return

		initialLoad: ->
			d = @$q.defer()

			@Api.sendDataGet({
				app: '/apps/instances/' + @instanceId
			}).then( (result) =>
				@app = result.data.app.app;

				@Api.sendDataGet({
					pack: '/apps/packages/' + @app.package_name
				}).then( (result) =>
					@pack = result.data.pack['package']
					d.resolve()
				)
			)

			d.promise.then(=>
				@$scope.pack = @pack
				@$scope.setting_values = @app.settings
				if not @$scope.setting_values || Util.isArray(@$scope.setting_values)
					@$scope.setting_values = {}
				@$scope.setting_values.dp_app = {title: @app.title}
			)

			d.promise


		###
    	# Saves settings
    	###
		saveSettings: ->
			postData = {
				settings: @$scope.setting_values
			}

			@startSpinner('saving_settings')
			@Api.sendPostJson("/apps/instances/#{@instanceId}", postData).then(=>
				@stopSpinner('saving_settings').then(=>
					@$scope.$parent.ListCtrl.updateAppTitle(@instanceId, @$scope.setting_values.dp_app.title)
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			, ->
				@stopSpinner('saving_settings')
			)


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
						@$scope.$parent?.ListCtrl.removeAppInstance(@app.id)

					# close this view
					@$state.go('apps.apps')
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

	Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL()