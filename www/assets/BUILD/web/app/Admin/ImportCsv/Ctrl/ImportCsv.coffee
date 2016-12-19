define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
  class Admin_ImportCsv_Ctrl_ImportCsv extends Admin_Ctrl_Base

    @CTRL_ID = 'Admin_ImportCsv_Ctrl_ImportCsv'
    @CTRL_AS = 'Ctrl'
    @DEPS = ['Api', 'Growl', '$http', '$interval']



    init: ->
      @$scope.fileUploadOptions = {url: @$http.formatApiUrl('/import_csv_upload'), disabled: true}
      @$scope.fileUploadResults = null
      @$scope.fileSelected = false
      @$scope.processStarted = false
      @$scope.importErrors = {}
      @$scope.importStarted = false
      @$scope.logs = []

      @$scope.delimeter = 'comma'
      @$scope.enclosure = 'none'
      @options = {}

      @$scope.importSettings = {fieldMappings: [], additionalMappings: [], skipFirst: 1, updateIfExists: 1, welcomeEmail: false, showExtraMappings: {}}
      @showExtraMappingsCases = [
        'organization', 'phone', 'website', 'im', 'twitter', 'linkedin', 'facebook', 'address1', 'address2', 'city',
        'state', 'zip', 'country', 'new_custom', 'language'
      ]

      for key in @showExtraMappingsCases
        @$scope.importSettings.showExtraMappings[key] = []

      @$scope.$on 'dp-status-update', (e, data) =>
        if data.status == 'disabled_on_demo'
          @$scope.disabledOnDemo = true
        else
          @$scope.fileUploadOptions = {url: @$http.formatApiUrl('/import_csv_upload'), disabled: false}
        @$scope.log = data.log
        @updateLogs()

      @$scope.$on '$destroy', => @interval && @$interval.cancel(@interval)

      @setupUploadListeners()



    initialLoad: ->
      @interval = @$interval (=> @updateLogs()), 5000
      @updateLogs()



    setupUploadListeners: ->
      @$scope.$on('fileuploaddone', (e, data) =>
        @$scope.fileUploadResults = data.result
        @$scope.fileSelected = false
        @$scope.fileUploadResults.upload_failed = true if @$scope.fileUploadResults.error

        if !@$scope.fileUploadResults.upload_failed
          @$scope.processStarted = true
          @$scope.$apply =>
            for key, idx in @$scope.fileUploadResults.columns
              @$scope.importSettings.additionalMappings[idx] =
                title: 'Custom Field'
                handler_class: 'text'
      )

      @$scope.$on('fileuploadfail', (e, data) =>
        @$scope.fileUploadResults = {}
        @$scope.fileUploadResults.upload_failed = true
        @$scope.fileSelected = false
      )

      @$scope.$on('fileuploadchange', (e, data) =>
        @$scope.fileSelected = true
      )



    startImport: ->
      field_maps = []

      # construct field mappings

      for value, key in @$scope.importSettings.fieldMappings

        obj = {map: value}
        for own key2, value2 of @$scope.importSettings.additionalMappings[key]
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
        update_if_exists: @$scope.importSettings.updateIfExists
        welcome_email: if welcome_email then 1 else 0,
        filename: filename
        options: options

      }).then((result) =>
        @stopSpinner('saving', true).then(=>
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
      for own key of @$scope.importSettings.showExtraMappings
        @$scope.importSettings.showExtraMappings[key][column_id] = false

      if @showExtraMappingsCases.indexOf(selected_field) > -1
        @$scope.importSettings.showExtraMappings[selected_field][column_id] = true



    updateLogs: ->
      @Api.sendGet('import_csv_logs').then (res) =>
        @$scope.logs.length = 0
        return if !res.data?.length
        res.data.map (item) =>
          item.date = new Date(item.data.started * 1000)
          item.time = if item.data.finished then moment(item.data.finished * 1000).from(item.data.started * 1000, true) else '-'
          @$scope.logs.push item



    startDeleteUsers: (name) ->
      deleteUsers = =>
        @Api.sendDelete 'import_csv_clean', {ref: name.replace('csv_import.', '')}

      message = @getRegisteredMessage 'delete_users_prompt'
      update = => @updateLogs()

      @$modal.open({
        templateUrl: @getTemplatePath('Index/modal-confirm.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.message = message

          $scope.confirm = (options) ->
            deleteUsers().then ->
              $modalInstance.dismiss()
              update()
        ]
      });



  Admin_ImportCsv_Ctrl_ImportCsv.EXPORT_CTRL()
