define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
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
				@$scope.setting_values.dp_app = {title: @app.title}
			)

			d.promise

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
			});

	Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL()