(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
    var Admin_ServerFileUploads_Ctrl_ServerFileUploads;
    Admin_ServerFileUploads_Ctrl_ServerFileUploads = (function(_super) {
      __extends(Admin_ServerFileUploads_Ctrl_ServerFileUploads, _super);

      function Admin_ServerFileUploads_Ctrl_ServerFileUploads() {
        return Admin_ServerFileUploads_Ctrl_ServerFileUploads.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_ID = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_AS = 'Ctrl';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.DEPS = ['$state', '$http'];

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.init = function() {
        this.$scope.data = null;
        this.$scope.fileUploadOptions = {};
        this.$scope.fileUploadResults = null;
        this.$scope.fileSelected = false;
        this.$scope.fileTransferStarted = false;
        return this.setupUploadListeners();
      };

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_file_uploads').then((function(_this) {
          return function(res) {
            _this.$scope.data = res.data.server_file_uploads;
            return _this.$scope.fileUploadOptions.url = _this.$http.signUrl(res.data.server_file_uploads.file_uploader_url);
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.setupUploadListeners = function() {
        this.$scope.$on('fileuploaddone', (function(_this) {
          return function(e, data) {
            _this.$scope.fileUploadResults = data.result;
            return _this.$scope.fileSelected = false;
          };
        })(this));
        this.$scope.$on('fileuploadfail', (function(_this) {
          return function(e, data) {
            _this.$scope.fileUploadResults = {};
            _this.$scope.fileUploadResults.upload_failed = true;
            return _this.$scope.fileSelected = false;
          };
        })(this));
        return this.$scope.$on('fileuploadchange', (function(_this) {
          return function(e, data) {
            return _this.$scope.fileSelected = true;
          };
        })(this));
      };


      /*
      		 * Show switch dlg
       */

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.startSwitchStorage = function() {
        var data, inst;
        data = this.$scope.data;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Server/server-file-uploads-switch-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              var validateS3Bucket;
              $scope.data = data;
              $scope.options = {
                method: data.filestorage_method,
                s3_bucket: data.s3_bucket,
                s3_key: data.s3_key,
                s3_secret: data.s3_secret
              };
              $scope.bucketNameTrans = function() {
                var key;
                key = $scope.options.s3_bucket || '';
                key = key.replace(/\s+/g, '-');
                key = key.replace(/[^a-zA-Z0-9\.\-]/g, '');
                key = key.replace(/([\.\-])[\.\-]+/g, '$1');
                key = key.toLowerCase();
                return $scope.options.s3_bucket = key;
              };
              validateS3Bucket = function() {
                var key;
                $scope.invalid_bucket_name = false;
                key = Strings.trim($scope.options.s3_bucket || '');
                if (key.length < 3 || key.length > 63) {
                  $scope.invalid_bucket_name = true;
                  return false;
                }
                if (!key.match(/^[a-z][a-z0-9\.\-]+[a-z0-9]$/)) {
                  $scope.invalid_bucket_name = true;
                  return false;
                }
                if (key.match(/[\.\-]{2,}/)) {
                  $scope.invalid_bucket_name = true;
                  return false;
                }
                return true;
              };
              $scope.confirm = function() {
                if ($scope.options.method === 's3') {
                  if (!validateS3Bucket()) {
                    return;
                  }
                }
                return $modalInstance.close($scope.options);
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then((function(_this) {
          return function(res) {
            return _this.switchStorage(res);
          };
        })(this));
      };


      /*
      		 * Actually do the switch
       */

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.switchStorage = function(options) {
        this.$scope.updating_method = true;
        return this.Api.sendPostJson('/server_file_uploads/switch', {
          options: options
        }).then((function(_this) {
          return function() {
            _this.$scope.updating_method = false;
            _this.$scope.data.filestorage_method = options.method;
            _this.$scope.data.s3_bucket = options.s3_bucket;
            _this.$scope.data.s3_key = options.s3_key;
            _this.$scope.data.s3_secret = options.s3_secret;
            _this.Growl.success('Transfering of files started');
            return _this.$scope.fileTransferStarted = true;
          };
        })(this));
      };

      return Admin_ServerFileUploads_Ctrl_ServerFileUploads;

    })(Admin_Ctrl_Base);
    return Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerFileUploads.js.map
