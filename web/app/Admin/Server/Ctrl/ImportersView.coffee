define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Server_Ctrl_ImportersView extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Server_Ctrl_ImportersView'
    @CTRL_AS = 'Ctrl'
    @DEPS = ['$upload', '$http', '$interval']

    init:        ->
      @$scope.id = @$stateParams.id
      @$scope.busy = false

      @$scope.$watch 'importer.status', (val) =>
        if val && 'testing' != val && 'done' != val
          @updateImportStatus = @$interval (=> @importGet()), 1000 if !@updateImportStatus
        else if @updateImportStatus
          @$interval.cancel @updateImportStatus
          @updateImportStatus = null

      @$scope.$on '$destroy', =>
        @$interval.cancel @updateImportStatus
        @updateImportStatus = null



    initialLoad: ->
      @importGet().then () =>
        if 'csv' == @$scope.importer?.id
          @$scope.$watch 'importer.config.blobs.length', (val) =>
            @$scope.ready = !!val
        else if 'zendesk' == @$scope.importer?.id
          @$scope.$watch(
            'importer.config'
            (val) =>
              @$scope.ready = val && val.subdomain && val.username && (val.password || val.token)
            true
          )
        else if 'osticket' == @$scope.importer?.id
          @$scope.$watch(
            'importer.config'
            (val) =>
              @$scope.ready = val && val.host && val.db && val.user && val.password
            true
          )



    deleteBlob: (blob) ->
      onDelete = =>
        blobs = @$scope.importer.config.blobs
        blobs.splice blobs.indexOf(blob), 1
        @importSave()

      @Api.sendDelete("/blobs/#{blob.id}/#{blob.authcode}").then onDelete, onDelete



    onFileSelect: (files) ->
      file = files[0]
      for blob in @$scope.importer.config.blobs
        return false if blob.filename == file.name

      @$scope.uploading = true
      @$upload.upload({
        url:  @$http.formatApiUrl '/misc/upload'
        file: file
      }).success((data) =>
        @$scope.uploading = false
        @$scope.importer.config.blobs.push
          id:       data.blob.id
          filename: data.blob.filename
          authcode: data.blob.authcode
        @importSave()
      ).error((data) =>
        @$scope.uploading = false
        @Growl.error data?.error_message || 'Error'
      )



    importGet: ->
      @Api.sendGet('/server/importers/' + @$scope.id).then (res) =>
        @$scope.importer = res.data



    importSave: (reset) ->
      url = '/server/importers/' + @$scope.id
      url += '?reset=1' if reset
      @Api.sendPutJson(url, @$scope.importer).then (res) =>
        @$scope.importer = res.data



    importReset: =>
      @importSave true



    importTest: ->
      @$scope.importer.status = 'testing'
      @$scope.busy = true
      @importSave().then () =>
        @Api.sendGet("/server/importers/#{@$scope.id}/test").then(
          (res) =>
            @$scope.busy = false
            @$scope.test_error = !res.data.result
            @$scope.test_error_message = res.data.error_message
          (res) =>
            @$scope.busy = false
            @$scope.test_error = true
        )



    importStart: ->
      @$scope.importer.status = 'pending'
      @importSave().then =>
        @Api.sendGet("/server/importers/#{@$scope.id}/start").then(
          (res) =>
            (res) =>
        )





  Admin_Server_Ctrl_ImportersView.EXPORT_CTRL()