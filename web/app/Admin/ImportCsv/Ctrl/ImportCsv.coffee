define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ImportCsv_Ctrl_ImportCsv extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ImportCsv_Ctrl_ImportCsv'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->

			@$scope.fileUploadOptions = {url: window.DP_BASE_API_URL + '/import_csv_upload'}
			@$scope.fileUploadResults = null
			@$scope.fileSelected = false

			@$scope.importSettings = {fieldMappings: [], additionalMappings: [], skipFirst: 1, showExtraMappings: {}}
			@showExtraMappingsCases = [
				'organization', 'phone', 'website', 'im', 'twitter', 'linkedin', 'facebook', 'address1', 'address2', 'city',
				'state', 'post_code', 'country', 'new_custom'
			]

			for key in @showExtraMappingsCases
				@$scope.importSettings.showExtraMappings[key] = []

			@setupUploadListeners()

			return

		###
		#
		###

		setupUploadListeners: ->

			@$scope.$on('fileuploaddone', (e, data) =>
				@$scope.fileUploadResults = data.result
				@$scope.fileSelected = false
				@$scope.fileUploadResults.upload_failed = true if @$scope.fileUploadResults.error

				if !@$scope.fileUploadResults.upload_failed
					for key, idx in @$scope.fileUploadResults.columns
						@$scope.importSettings.additionalMappings[idx] = {}
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
 	# Handler for selection of field mapping
 	# Shows / hides appropriate extra mapping for mappings table, could add extra functionality here later
 	#
 	# @param {Integer} column_id - id of column from the table with mapping
 	# @param {String} selected_field - name of field sent by 'ng-change'
		###

		selectMapping: (column_id, selected_field) ->

			for key of @$scope.importSettings.showExtraMappings
				@$scope.importSettings.showExtraMappings[key][column_id] = false

			if @showExtraMappingsCases.indexOf(selected_field) > -1
				@$scope.importSettings.showExtraMappings[selected_field][column_id] = true

	Admin_ImportCsv_Ctrl_ImportCsv.EXPORT_CTRL()