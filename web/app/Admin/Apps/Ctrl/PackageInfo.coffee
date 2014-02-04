define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Apps_Ctrl_PackageInfo extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Apps_Ctrl_PackageInfo'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$http']

		init: ->
			@packageName = @$stateParams.name;
			@aceEditors = {}

			@$scope.package_assets = {
				app_js: "",
				readme: ""
			}

			createAceLoaded = (name, maxH = 500) =>
				return (editor) =>
					@aceEditors[name] = editor
					updateH = ->
						newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth()
						if newHeight > maxH
							newHeight = maxH
						if newHeight < 300
							newHeight = 300

						$(editor.container).height(newHeight)
						editor.resize()

					updateH()
					editor.getSession().on('change', updateH);
					editor.setShowPrintMargin(false)

					$(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor')

			@$scope.aceLoaded = {
				app_js: createAceLoaded('app_js')
			}
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				pack: '/apps/packages/' + @packageName,
			}).then( (result) =>
				@pack = result.data.pack['package']
				getResource = (id, tag) =>
					asset = @pack.assets.filter((x) -> x.tag == tag)[0]
					if asset
						@$http.get(asset.blob.relative_url, { responseType: "text"}).success((data) =>
							@$scope.package_assets[id] = data
						)

				getResource('app_js', 'app_js')
			)

			return promise

		###
    	# Shows the install overlay
    	###
		startInstall: ->
			if @$scope.$parent.ListCtrl?.addAppInstance?
				listCtrl = @$scope.$parent.ListCtrl

			doInstall = (setting_values) =>
				return @Api.sendPut("/apps/packages/#{@packageName}", {settings: setting_values}).success( (info) =>

					if listCtrl
						instanceInfo = {
							id: info.id,
							title: setting_values.dp_app.title,
							package_name: @pack.name,
							package: @pack
						}
						listCtrl.addAppInstance(instanceInfo)

					@$state.go('apps.apps.instance', {id: info.id});
				);

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Apps/package-install-modal.html'),
				controller: ['$scope', '$modalInstance', 'pack', ($scope, $modalInstance, pack) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doInstall = ->
						$scope.is_loading = true
						doInstall($scope.setting_values).then(->
							$modalInstance.dismiss()
						, ->
							$scope.is_loading = false
						)

					$scope.pack = pack
					$scope.setting_values = { dp_app: { title: pack.title }}

					for setting in pack.settings_def
						if setting.default_value
							$scope.setting_values[setting.name] = setting.default_value
				],
				resolve: {
					pack: =>
						return @pack
				}
			});

	Admin_Apps_Ctrl_PackageInfo.EXPORT_CTRL()