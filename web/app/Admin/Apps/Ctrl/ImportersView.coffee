define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Apps_Ctrl_ImportersView extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Apps_Ctrl_ImportersView'
    @CTRL_AS = 'Ctrl'
    @DEPS = ['$upload', '$http', '$interval']

    init:        ->
      @$scope.id = @$stateParams.id
      @$scope.busy = false

      @$scope.$watch 'importer.logfile', (val) =>
        @$scope.log_download_url = if val then @$http.formatApiUrl('/server/importers/'+@$scope.id+'/download-log') else null

      @$scope.$watch 'importer.status', (val) =>
        if val && 'testing' != val && 'done' != val && 'error' != val
          @updateImportStatus = @$interval (=> @importGet()), 5000 if !@updateImportStatus
        else if @updateImportStatus
          @$interval.cancel @updateImportStatus
          @updateImportStatus = null

        if 'done' == val || 'error' == val
          @$scope.done = true

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
        else if 'osticket' == @$scope.importer?.id || 'deskpro' == @$scope.importer?.id
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
      @$scope.file_upload_error = null;

      file = files[0]
      for blob in @$scope.importer.config.blobs
        return false if blob.filename == file.name

      if @$scope.id == 'csv'
        allowed = [
          'articles.csv',
          'article_categories.csv',
          'article_custom_fields.csv',
          'downloads.csv',
          'downloads_attachments.csv',
          'feedback.csv',
          'feedback_attachments.csv',
          'feedback_custom_fields.csv',
          'news.csv',
          'people.csv',
          'people_contact_data.csv',
          'people_custom_fields.csv',
          'tickets.csv',
          'ticket_messages.csv',
          'ticket_attachments.csv',
          'ticket_custom_fields.csv',
          'organizations.csv',
          'organization_contact_data.csv',
          'organization_custom_fields.csv',
        ]
        f = files[0].name.toLowerCase()
        if allowed.indexOf(f) == -1 && 'application/zip' != files[0].type
          @$scope.file_upload_error = 'The file you selected (' + f + ') does not match any of the expected files this importer supports.';
          return

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
        updated = @$scope.importer.updated
        if updated && Math.round(Date.now() / 1000) > updated + 600
          return @$scope.importer.status = 'error'



    importSave: (reset) ->
      url = '/server/importers/' + @$scope.id
      url += '?reset=1' if reset
      @Api.sendPutJson(url, @$scope.importer).then (res) =>
        @$scope.importer = res.data



    importReset: =>
      @$scope.done = false
      @importSave true



    importTest: ->
      @$scope.busy = true
      @importSave().then(
        =>
          @$scope.importer.status = 'testing'
          @Api.sendGet("/server/importers/#{@$scope.id}/test").then(
            (res) =>
              @$scope.busy = false
              @$scope.test_error = !res.data.result
              @$scope.test_error_message = res.data.error_message
            (res) =>
              @$scope.busy = false
              @$scope.test_error = true
          )
        (res) =>
          @$scope.busy = false
          @$scope.test_error = true
      )





    importStart: ->
      @Api.sendGet("/server/importers/#{@$scope.id}/start").then(
        (res) => @$scope.importer = res.data
        (res) =>
      )





  Admin_Apps_Ctrl_ImportersView.EXPORT_CTRL()