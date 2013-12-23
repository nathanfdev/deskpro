define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ImportCsv_Ctrl_ImportCsv extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ImportCsv_Ctrl_ImportCsv'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->

			@$scope.fileUploadOptions = {url: window.DP_BASE_API_URL + '/import_csv_upload'}
			@$scope.fileUploadResults = null
			@$scope.fileSelected = false

			@$scope.importSettings = {fieldMappings:[], skipFirst: 1}

			@setupUploadListeners()

			return

		###
 	#
		###

		initialLoad: ->

			return

		###
		#
		###

		setupUploadListeners: ->

			@$scope.$on('fileuploaddone', (e, data) =>
				@$scope.fileUploadResults = data.result
				@$scope.fileSelected = false
				@$scope.fileUploadResults.upload_failed = true if @$scope.fileUploadResults.error
			)

			@$scope.$on('fileuploadfail', (e, data) =>
				@$scope.fileUploadResults = {}
				@$scope.fileUploadResults.upload_failed = true
				@$scope.fileSelected = false
			)

			@$scope.$on('fileuploadchange', (e, data) =>
				@$scope.fileSelected = true
			)

		###
 	#
		###

		selectMapping: (column_id, selected_field) ->

			alert column_id + ' ' + selected_field

	Admin_ImportCsv_Ctrl_ImportCsv.EXPORT_CTRL()