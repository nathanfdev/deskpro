define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerFileUploads_Ctrl_ServerFileUploads extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$state', '$http']

		init: ->
			@$scope.data = null
			@$scope.fileUploadOptions = {}
			@$scope.fileUploadResults = null
			@$scope.fileSelected = false
			@$scope.fileTransferStarted = false
			@setupUploadListeners()


		initialLoad: ->
			data_promise = @Api.sendGet('/server_file_uploads').then( (res) =>
				@$scope.data = res.data.server_file_uploads
				@$scope.fileUploadOptions.url = @$http.signUrl(res.data.server_file_uploads.file_uploader_url)
			)

			return @$q.all([data_promise])


		setupUploadListeners: ->
			@$scope.$on('fileuploaddone', (e, data) =>
				@$scope.fileUploadResults = data.result;
				@$scope.fileSelected = false
			)

			@$scope.$on('fileuploadfail', (e, data) =>
				@$scope.fileUploadResults = {}
				@$scope.fileUploadResults.upload_failed = true;
				@$scope.fileSelected = false
			)

			@$scope.$on('fileuploadchange', (e, data) =>
				@$scope.fileSelected = true
			)


		###
		# Show switch dlg
		###
		startSwitchStorage: ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('Server/server-file-uploads-switch-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then(() =>
				@switchStorage()
			)


		###
		# Actually do the switch
		###
		switchStorage: ->
			@Api.sendPost('/server_file_uploads/switch').then( =>
				@Growl.success('Transfering of files started')
				@$scope.fileTransferStarted = true
			)

	Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL()