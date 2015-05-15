define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Server_Ctrl_ImportersView extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Server_Ctrl_ImportersView'
    @CTRL_AS = 'ImportersViewCtrl'
    @DEPS = ['$upload', '$http']

    init:        ->
      @$scope.id = @$stateParams.id
      @$scope.step = 0
      @$scope.$watch 'importer.config.blobs.length', (val) =>
        @$scope.ready = if val then true else false



    initialLoad: ->
      @Api.sendGet('/server/importers/' + @$scope.id).then (res) =>
        @$scope.importer = res.data



    saveImporterConfig: ->
      @Api.sendPutJson '/server/importers/' + @$scope.id, @$scope.importer



    deleteBlob: (blob) ->
      onDelete = =>
        blobs = @$scope.importer.config.blobs
        blobs.splice blobs.indexOf(blob), 1
        @saveImporterConfig()

      @Api.sendDelete("/blobs/#{blob.id}/#{blob.authcode}").then onDelete, onDelete



    startTest: ->
      @$scope.step = 1
      @Api.sendGet "/server/importers/#{@$scope.id}/test"



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
        @saveImporterConfig()
      ).error((data) =>
        @$scope.uploading = false
        @Growl.error data?.error_message || 'Error'
      )


  Admin_Server_Ctrl_ImportersView.EXPORT_CTRL()