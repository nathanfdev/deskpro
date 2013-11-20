define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerFileUploads_Ctrl_ServerFileUploads extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$state']

		init: ->

			@$scope.data = null
			@$scope.fileUploadOptions = {}
			@$scope.fileUploadResults = null

			@setupUploadListeners()

		###
 	#
 	###

		initialLoad: ->
			data_promise = @Api.sendGet('/server_file_uploads').then( (res) =>

				@$scope.data = res.data.server_file_uploads
				@$scope.fileUploadOptions.url = res.data.server_file_uploads.file_uploader_url
			)

			return @$q.all([data_promise])

		###
 	#
		###

		setupUploadListeners: ->

			@$scope.$on('fileuploaddone', (e, data) =>
					@$scope.fileUploadResults = data.result;
			)

			@$scope.$on('fileuploadfail', (e, data) =>
				@$scope.fileUploadResults = {}
				@$scope.fileUploadResults.upload_failed = true;
			)

		###
		# Show switch dlg
		###

		startSwitchStorage: ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('ServerFileUploads/switch-modal.html'),
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

				@initialLoad()
			)

	Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL()