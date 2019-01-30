// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
  class Admin_ServerFileUploads_Ctrl_ServerFileUploads extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$state', '$http'];
    }

    init() {
      this.$scope.data = null;
      this.$scope.fileUploadOptions = {};
      this.$scope.fileUploadResults = null;
      this.$scope.fileSelected = false;
      this.$scope.fileTransferStarted = false;
      this.setupUploadListeners();

      return this.$scope.tmpIsNotWritable = () => (this.$scope.fileUploadResults.is_tmp_writable !== undefined) && !this.$scope.fileUploadResults.is_tmp_writable;
    }


    initialLoad() {
      const data_promise = this.Api.sendGet('/server_file_uploads').then( res => {
        this.$scope.data = res.data.server_file_uploads;
        return this.$scope.fileUploadOptions.url = this.$http.signUrl(res.data.server_file_uploads.file_uploader_url);
      });

      return this.$q.all([data_promise]);
    }


    setupUploadListeners() {
      this.$scope.$on('fileuploaddone', (e, data) => {
        this.$scope.fileUploadResults = data.result;
        return this.$scope.fileSelected = false;
      });

      this.$scope.$on('fileuploadfail', (e, data) => {
        this.$scope.fileUploadResults = {
          upload_failed: true,
          error_message: data.errorThrown,
          upload_status: 413,
          response_headers: data.jqXHR.getAllResponseHeaders()
        };
        return this.$scope.fileSelected = false;
      });

      return this.$scope.$on('fileuploadchange', (e, data) => {
        return this.$scope.fileSelected = true;
      });
    }


    /*
     * Show switch dlg
     */
    startSwitchStorage() {
      const { data } = this.$scope;
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Server/server-file-uploads-switch-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.data = data;
          $scope.options = {
            method:      data.filestorage_method,
            s3_bucket:   data.s3_bucket,
            s3_key:      data.s3_key,
            s3_secret:   data.s3_secret,
            s3_region:   data.s3_region,
            s3_endpoint: data.s3_endpoint,
            s3_file_url_template: data.s3_file_url_template,
            s3_credentials_source: data.s3_credentials_source
          };

          $scope.bucketNameTrans = function() {
            let key = $scope.options.s3_bucket || '';
            key = key.replace(/\s+/g, '-');
            key = key.replace(/[^a-zA-Z0-9\.\-]/g, '');
            key = key.replace(/([\.\-])[\.\-]+/g, '$1');
            key = key.toLowerCase();
            return $scope.options.s3_bucket = key;
          };

          // see https://aws.amazon.com/articles/Amazon-S3/1904 "Naming Buckets and Keys"
          const validateS3Bucket = function() {
            $scope.invalid_bucket_name = false;
            $scope.invalid_bucket_region = false;
            $scope.invalid_access_key = false;
            $scope.invalid_access_secret = false;
            $scope.form_invalid = false;

            const key = Strings.trim($scope.options.s3_bucket || '');

            // length between 3 and 63
            if ((key.length < 3) || (key.length > 63)) {
              $scope.invalid_bucket_name = true;
            // start with letter, then lettes/numbers/./-, then end with letter or number
            } else if (!key.match(/^[a-z][a-z0-9\.\-]+[a-z0-9]$/)) {
              $scope.invalid_bucket_name = true;
            // multiple dots or dashes in sequence: abc.....ef
            } else if (key.match(/[\.\-]{2,}/)) {
              $scope.invalid_bucket_name = true;
            }

            if (!$scope.options.s3_region) {
              $scope.invalid_bucket_region = true;
            }

            if ($scope.isAccessKeysEnabled()) {
              if (!$scope.options.s3_secret) {
                $scope.invalid_access_secret = true;
              }
              if (!$scope.options.s3_key) {
                $scope.invalid_access_key = true;
              }
            }

            $scope.form_invalid = $scope.invalid_bucket_name || $scope.invalid_bucket_region 
                                  || $scope.invalid_access_key || $scope.invalid_access_secret;
            
            return !$scope.form_invalid;
          };

          $scope.isAccessKeysEnabled = () => !$scope.options.s3_credentials_source || ($scope.options.s3_credentials_source.toLowerCase() !== 'ec2');

          $scope.confirm = function() {
            if ($scope.options.method === 's3') {
              if (!validateS3Bucket()) { return; }
            }

            return $modalInstance.close($scope.options);
          };

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(res => {
        return this.switchStorage(res);
      });
    }


    /*
     * Actually do the switch
     */
    switchStorage(options) {
      this.$scope.updating_method = true;
      if (options.s3_endpoint && (options.s3_endpoint.toLowerCase().indexOf('http') !== 0)) {
        options.s3_endpoint = `https://${options.s3_endpoint}`;
      }

      return this.Api.sendPostJson('/server_file_uploads/switch', {options}).then( () => {
        this.$scope.updating_method = false;
        this.$scope.data.filestorage_method = options.method;
        this.$scope.data.s3_bucket   = options.s3_bucket;
        this.$scope.data.s3_key      = options.s3_key;
        this.$scope.data.s3_secret   = options.s3_secret;
        this.$scope.data.s3_region   = options.s3_region;
        this.$scope.data.s3_endpoint = options.s3_endpoint;
        this.$scope.data.s3_file_url_template = options.s3_file_url_template;
        this.$scope.data.s3_credentials_source = options.s3_credentials_source;

        this.Growl.success('Transfering of files started');
        return this.$scope.fileTransferStarted = true;
      });
    }
  }
  Admin_ServerFileUploads_Ctrl_ServerFileUploads.initClass();

  return Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL();
});