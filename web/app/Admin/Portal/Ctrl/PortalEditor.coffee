define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
		@CTRL_AS = 'Portal'

		init: ->
			@portal_enabled = false
			return

		initialLoad: ->
			promise = @Api.sendGet('/portal_settings').then( (result) =>
				settings = result.data.portal_settings
				@portal_enabled  = settings.portal_enabled
				@favicon_url     = settings.favicon_blob_url || '/favicon.ico'
				@favicon_blob_id = settings.favicon_blob_id || null
			)
			return promise

		startTogglePortal: ->
			if @portal_enabled
				message = "Are are sure you want to disable the portal? The front-end portal website for end-users will be completely disabled. Anyone who knows the URL of the portal will see a blank page."
			else
				message = "Are are sure you want to enable the portal?"

			@showConfirm(message).result.then( =>
				@togglePortal()
			)
			return

		togglePortal: ->
			newVal = if @portal_enabled then '0' else '1'

			@Api.sendPost('/settings/values/user.portal_enabled', { value: newVal }).then( =>
				@$state.go('portal.portal_editor_go')
			)

		openFaviconEditor: ->

			if @favicon_blob_id
				favi_type = 'custom'
			else
				favi_type = 'default'

			portalCtrl = @
			inst = @$modal.open({
				templateUrl: @getTemplatePath('PortalEditor/change-favicon-modal.html'),
				controller: ['$scope', '$modalInstance', '$http', ($scope, $modalInstance, $http) =>

					$scope.upload_url = $http.formatApiUrl('/blobs')
					$scope.fileSelected = false
					$scope.fileTransferStarted = false
					$scope.favicon_choice = favi_type
					$scope.favicon_saving = false
					if portalCtrl.favicon_blob_id
						$scope.custom_favicon_blob_url = portalCtrl.favicon_url
					$scope.form = {
						favicon_choice: favi_type
					}

					$scope.$on('fileuploaddone', (e, data) =>
						if data.result.blob?.id and data.result.blob.is_image
							$scope.new_blob_id   = data.result.blob.id
							$scope.new_blob_auth = data.result.blob.authcode
							$scope.new_blob_url  = data.result.blob.thumbnail_url_16
						else
							$scope.new_uploaded_failed = true
							$scope.new_blob_id = null
							$scope.fileSelected = false
					)

					$scope.$on('fileuploadfail', (e, data) =>
						$scope.new_uploaded_failed = true
						$scope.new_blob_id = null
						$scope.fileSelected = false
					)

					$scope.$on('fileuploadchange', (e, data) =>
						$scope.new_uploaded_failed = false
						$scope.new_blob_id = null
						$scope.new_blob_url = null
						$scope.fileSelected = true
					)

					$scope.confirm = ->
						if $scope.form.favicon_choice == favi_type or $scope.form.favicon_choice == 'new' && !$scope.new_blob_id
							$modalInstance.close()
							return

						blob_id   = if $scope.form.favicon_choice == 'default' then 0 else $scope.new_blob_id
						blob_auth = if $scope.form.favicon_choice == 'default' then '0' else $scope.new_blob_auth
						$scope.favicon_saving = true
						portalCtrl.Api.sendPost("/portal_settings/favicon/#{blob_id}/#{blob_auth}").then(=>
							$scope.favicon_saving = false
							portalCtrl.favicon_url = if blob_id == 0 then '/favicon.ico' else $scope.new_blob_url
							portalCtrl.favicon_blob_id = blob_id || null
							$modalInstance.close()
						)

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

	Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()