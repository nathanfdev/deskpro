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

      @$scope.tmpIsNotWritable = () => @$scope.fileUploadResults.is_tmp_writable != undefined && !@$scope.fileUploadResults.is_tmp_writable


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
        @$scope.fileUploadResults = {
          upload_failed: true
          error_message: data.errorThrown
          upload_status: 413,
          response_headers: data.jqXHR.getAllResponseHeaders()
        }
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
            method:      data.filestorage_method,
            s3_bucket:   data.s3_bucket,
            s3_key:      data.s3_key,
            s3_secret:   data.s3_secret,
            s3_region:   data.s3_region,
            s3_endpoint: data.s3_endpoint,
            s3_file_url_template: data.s3_file_url_template,
            s3_credentials_source: data.s3_credentials_source
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
            $scope.invalid_bucket_region = false
            $scope.invalid_access_key = false
            $scope.invalid_access_secret = false
            $scope.form_invalid = false

            key = Strings.trim($scope.options.s3_bucket || '')

            # length between 3 and 63
            if key.length < 3 or key.length > 63
              $scope.invalid_bucket_name = true
            # start with letter, then lettes/numbers/./-, then end with letter or number
            else if not key.match(/^[a-z][a-z0-9\.\-]+[a-z0-9]$/)
              $scope.invalid_bucket_name = true
            # multiple dots or dashes in sequence: abc.....ef
            else if key.match(/[\.\-]{2,}/)
              $scope.invalid_bucket_name = true

            if not $scope.options.s3_region
              $scope.invalid_bucket_region = true

            if $scope.isAccessKeysEnabled()
              if not $scope.options.s3_secret
                $scope.invalid_access_secret = true
              if not $scope.options.s3_key
                $scope.invalid_access_key = true

            $scope.form_invalid = $scope.invalid_bucket_name || $scope.invalid_bucket_region \
                                  || $scope.invalid_access_key || $scope.invalid_access_secret;
            
            return !$scope.form_invalid

          $scope.isAccessKeysEnabled = ->
            return !$scope.options.s3_credentials_source || $scope.options.s3_credentials_source.toLowerCase() != 'ec2';

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
      if options.s3_endpoint && options.s3_endpoint.toLowerCase().indexOf('http') != 0
        options.s3_endpoint = 'https://' + options.s3_endpoint

      @Api.sendPostJson('/server_file_uploads/switch', {options: options}).then( =>
        @$scope.updating_method = false
        @$scope.data.filestorage_method = options.method
        @$scope.data.s3_bucket   = options.s3_bucket
        @$scope.data.s3_key      = options.s3_key
        @$scope.data.s3_secret   = options.s3_secret
        @$scope.data.s3_region   = options.s3_region
        @$scope.data.s3_endpoint = options.s3_endpoint
        @$scope.data.s3_file_url_template = options.s3_file_url_template
        @$scope.data.s3_credentials_source = options.s3_credentials_source

        @Growl.success('Transfering of files started')
        @$scope.fileTransferStarted = true
      )

  Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL()