define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ImportCsv_Ctrl_ImportCsv extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ImportCsv_Ctrl_ImportCsv'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['Api', 'Growl', '$http']

		init: ->

			@$scope.fileUploadOptions = {url: @$http.formatApiUrl('/import_csv_upload') }
			@$scope.fileUploadResults = null
			@$scope.fileSelected = false
			@$scope.processStarted = false
			@$scope.importErrors = {}
			@$scope.importStarted = false

			@$scope.delimeter = 'comma'
			@$scope.enclosure = 'none'
			@options = {}

			@$scope.importSettings = {fieldMappings: [], additionalMappings: [], skipFirst: 1, welcomeEmail: false, showExtraMappings: {}}
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
					@$scope.processStarted = true
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
 	# Sends requests to launch a task for starting CSV import
 	###

		startImport: ->

			field_maps = []

			# construct field mappings

			for value, key in @$scope.importSettings.fieldMappings

				obj = {map: value}
				for key2, value2 of @$scope.importSettings.additionalMappings[key]
					obj[key2] = value2

				field_maps.push(obj)

			# construct other needed variables

			user_filename = @$scope.fileUploadResults.user_filename
			skip_first = @$scope.importSettings.skipFirst
			welcome_email = @$scope.importSettings.welcomeEmail
			filename = @$scope.fileUploadResults.filename
			options = @$scope.fileUploadResults.options

			# sending the request and doing other actions like showing / hiding indicators etc.

			@startSpinner('saving')

			@Api.sendPostJson('import_csv_import', {

				field_maps: field_maps
				user_filename: user_filename
				skip_first: skip_first
				welcome_email: if welcome_email then 1 else 0,
				filename: filename
				options: options

			}).then( (result) =>

				@stopSpinner('saving', true).then( =>

					if result.data.error
						@$scope.importErrors.no_email = true if result.data.error == 'no_email'
						@$scope.importErrors.no_move = true if result.data.error == 'no_move'

					if result.data.success
						@$scope.importStarted = true
						@$scope.importErrors = {}
						@Growl.success("Importing started")
				)
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