define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Server_Ctrl_ImportersView extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Server_Ctrl_ImportersView'
    @CTRL_AS = 'Ctrl'
    @DEPS = ['$upload', '$http', '$interval']

    init:        ->
      @$scope.id = @$stateParams.id
      @$scope.busy = false

      @$scope.$watch 'importer.config.blobs.length', (val) =>
        @$scope.ready = if val then true else false

      @$scope.$watch 'step', (val) =>
        if 2 == val && !@updateImportStatus
          @updateImportStatus = @$interval (=> @importGet()), 1000
        else if @updateImportStatus
          @$interval.cancel @updateImportStatus
          @updateImportStatus = null

      @$scope.$on '$destroy', =>
        @$interval.cancel @updateImportStatus
        @updateImportStatus = null



    initialLoad: ->
      @importGet()



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
        if @$scope.importer.status?
          @$scope.step = 2
        else
          @$scope.step = 0



    importSave: ->
      @Api.sendPutJson '/server/importers/' + @$scope.id, @$scope.importer



    importReset: =>
      @$scope.step = 0



    importTest: ->
      @$scope.step = 1
      @$scope.busy = true
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
      @$scope.step = 2
      @Api.sendGet("/server/importers/#{@$scope.id}/start").then(
        (res) => @$scope.importer = res.data.importer
        (res) =>
      )





  Admin_Server_Ctrl_ImportersView.EXPORT_CTRL()