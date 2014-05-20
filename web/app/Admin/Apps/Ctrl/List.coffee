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

				# Dont list custom apps as "packages"
				@packages = @packages.filter((x) -> !x.is_custom)
				@general_packages = @packages.filter((x) -> !(x.tags.indexOf('usersources') != -1 and x.tags.length == 1))
				@auth_packages = @packages.filter((x) -> x.tags.indexOf('usersources') != -1)

				@apps = result.data.apps.apps.filter((x) -> !x.package.is_custom)
				@custom_apps = result.data.apps.apps.filter((x) -> x.package.is_custom)
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

		removeAppInstance: (instanceId) ->
			app = @apps.find((x) -> return x.id == instanceId)
			@apps = @apps.filter((x) -> return x.id != instanceId)
			@custom_apps = @custom_apps.filter((x) -> return x.id != instanceId)

			# we just removed an app so we might need to switch the
			# is_installed flag on the package so it appears back in the list
			if app
				hasOtherApp = false
				@apps.map((x) -> if x.package.name == app.package.name then hasOtherApp = true)
				if not hasOtherApp
					p = @packages.find((x) -> x.name == app.package.name)
					if p
						p.is_installed = false


		updateAppTitle: (id, title) ->
			@apps.filter((x) -> x.id == id).map((x) -> x.title = title)
			@custom_apps.filter((x) -> x.id == id).map((x) -> x.title = title)

		ensureCustomAppInList: (customApp) ->
			if not @custom_apps then return
			exist = @custom_apps.filter((x) -> x.id == customApp.id)
			if !exist.length
				@custom_apps.push(customApp)

		showNewApp: ->
			saveNewApp = (options) =>
				postData = {
					options: options
				}
				@Api.sendPutJson('/apps/custom', postData).success( (info) =>
					@$state.go('apps.apps.custom_instance', {custom_id: "custom_" + info.id});
				)

			@$modal.open({
				templateUrl: @getTemplatePath('Apps/new-app-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doCreate = ->
						$scope.is_loading = true
						saveNewApp($scope.opt).then(->
							$modalInstance.dismiss()
							$scope.is_loading = false
						, ->
							$scope.is_loading = false
						)

					$scope.opt = {
						ticket: {}
					}
				]
			});

		showUploadApp: ->

			me = @
			@$modal.open({
				templateUrl: @getTemplatePath('Apps/upload-package-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->

					uploadDone = (data) ->
						$modalInstance.dismiss()
						me.$timeout(->
							me.$state.go('apps.go_apps_install', {name: data.package_name})
						, 250)

					uploadError = (data) ->
						$scope.form.error = data?.error_code || 'general'

					$scope.form = {}
					$scope.form.upload_type = 'upload'
					$scope.form.is_active = false

					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.fileUploadOptions = {
						singleFileUploads: true,
						limitMultiFileUploads: 1,
						formData: {
							"API-TOKEN": window.DP_API_TOKEN,
							"REQUEST-TOKEN": window.DP_REQUEST_TOKEN,
							"SESSION-ID": window.DP_SESSION_ID
						}
					}

					$scope.$on('fileuploaddone', (e, data) ->
						$scope.form.is_active = false
						uploadDone(data.result, $modalInstance)

					)
					$scope.$on('fileuploadfail', (e, data) ->
						$scope.form.is_active = false
						uploadError(data.result || {}, $modalInstance)
					)

					$scope.startUpload = ->
						$scope.form.error = null
						if $scope.form.upload_type == 'upload'
							$scope.form.is_active = true
							$scope.form.uploadScope.submit()
						else
							$scope.form.is_active = true
							me.Api.sendPost('/apps/upload-package', { file_url: $scope.form.upload_url }).then( (result) ->
								$scope.form.is_active = false
								uploadDone(result.data, $modalInstance)
							, (result) ->
								$scope.form.is_active = false
								uploadError(result.data || {}, $modalInstance)
							)
				]
			});

	Admin_Apps_Ctrl_List.EXPORT_CTRL()