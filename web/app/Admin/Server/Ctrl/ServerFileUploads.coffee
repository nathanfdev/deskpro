define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) ->
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
      data = @$scope.data
      inst = @$modal.open({
        templateUrl: @getTemplatePath('Server/server-file-uploads-switch-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.data = data
          $scope.options = {
            method:    data.filestorage_method,
            s3_bucket: data.s3_bucket,
            s3_key:    data.s3_key,
            s3_secret: data.s3_secret
          }

          $scope.bucketNameTrans = ->
            key = $scope.options.s3_bucket || ''
            key = key.replace(/\s+/g, '-')
            key = key.replace(/[^a-zA-Z0-9\.\-]/g, '')
            key = key.replace(/([\.\-])[\.\-]+/g, '$1')
            key = key.toLowerCase()
            $scope.options.s3_bucket = key

          # see https://aws.amazon.com/articles/Amazon-S3/1904 "Naming Buckets and Keys"
          validateS3Bucket = ->
            $scope.invalid_bucket_name = false

            key = Strings.trim($scope.options.s3_bucket || '')

            # length between 3 and 63
            if key.length < 3 or key.length > 63
              $scope.invalid_bucket_name = true
              return false

            # start with letter, then lettes/numbers/./-, then end with letter or number
            if not key.match(/^[a-z][a-z0-9\.\-]+[a-z0-9]$/)
              $scope.invalid_bucket_name = true
              return false

            # multiple dots or dashes in sequence: abc.....ef
            if key.match(/[\.\-]{2,}/)
              $scope.invalid_bucket_name = true
              return false

            return true

          $scope.confirm = ->
            if $scope.options.method == 's3'
              return if not validateS3Bucket()

            $modalInstance.close($scope.options)

          $scope.dismiss = ->
            $modalInstance.dismiss()
        ]
      });

      inst.result.then((res) =>
        @switchStorage(res)
      )


    ###
    # Actually do the switch
    ###
    switchStorage: (options) ->
      @$scope.updating_method = true
      @Api.sendPostJson('/server_file_uploads/switch', {options: options}).then( =>
        @$scope.updating_method = false
        @$scope.data.filestorage_method = options.method
        @$scope.data.s3_bucket = options.s3_bucket
        @$scope.data.s3_key    = options.s3_key
        @$scope.data.s3_secret = options.s3_secret

        @Growl.success('Transfering of files started')
        @$scope.fileTransferStarted = true
      )

  Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL()