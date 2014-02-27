(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerFileUploads_Ctrl_ServerFileUploads;
    Admin_ServerFileUploads_Ctrl_ServerFileUploads = (function(_super) {
      __extends(Admin_ServerFileUploads_Ctrl_ServerFileUploads, _super);

      function Admin_ServerFileUploads_Ctrl_ServerFileUploads() {
        return Admin_ServerFileUploads_Ctrl_ServerFileUploads.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_ID = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_AS = 'Ctrl';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.DEPS = ['$state'];

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.init = function() {
        this.$scope.data = null;
        this.$scope.fileUploadOptions = {};
        this.$scope.fileUploadResults = null;
        this.$scope.fileSelected = false;
        this.$scope.fileTransferStarted = false;
        return this.setupUploadListeners();
      };


      /*
       	 *
       */

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_file_uploads').then((function(_this) {
          return function(res) {
            _this.$scope.data = res.data.server_file_uploads;
            return _this.$scope.fileUploadOptions.url = res.data.server_file_uploads.file_uploader_url;
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
       	 *
       */

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
        var inst;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Server/server-file-uploads-switch-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then((function(_this) {
          return function() {
            return _this.switchStorage();
          };
        })(this));
      };


      /*
      		 * Actually do the switch
       */

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.switchStorage = function() {
        return this.Api.sendPost('/server_file_uploads/switch').then((function(_this) {
          return function() {
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
