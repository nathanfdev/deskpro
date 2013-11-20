(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerFileUploads_Ctrl_ServerFileUploads, _ref;
    Admin_ServerFileUploads_Ctrl_ServerFileUploads = (function(_super) {
      __extends(Admin_ServerFileUploads_Ctrl_ServerFileUploads, _super);

      function Admin_ServerFileUploads_Ctrl_ServerFileUploads() {
        _ref = Admin_ServerFileUploads_Ctrl_ServerFileUploads.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_ID = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_AS = 'Ctrl';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.DEPS = [];

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.init = function() {
        this.$scope.data = null;
        this.$scope.fileUploadOptions = {};
        this.$scope.fileUploadResults = null;
        return this.setupUploadListeners();
      };

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendGet('/server_file_uploads').then(function(res) {
          _this.$scope.data = res.data.server_file_uploads;
          return _this.$scope.fileUploadOptions.url = res.data.server_file_uploads.file_uploader_url;
        });
        return this.$q.all([data_promise]);
      };

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.setupUploadListeners = function() {
        var _this = this;
        this.$scope.$on('fileuploaddone', function(e, data) {
          return _this.$scope.fileUploadResults = data.result;
        });
        return this.$scope.$on('fileuploadfail', function(e, data) {
          _this.$scope.fileUploadResults = {};
          return _this.$scope.fileUploadResults.upload_failed = true;
        });
      };

      return Admin_ServerFileUploads_Ctrl_ServerFileUploads;

    })(Admin_Ctrl_Base);
    return Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ServerFileUploads.js.map
*/