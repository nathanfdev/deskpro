(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ImportCsv_Ctrl_ImportCsv, _ref;
    Admin_ImportCsv_Ctrl_ImportCsv = (function(_super) {
      __extends(Admin_ImportCsv_Ctrl_ImportCsv, _super);

      function Admin_ImportCsv_Ctrl_ImportCsv() {
        _ref = Admin_ImportCsv_Ctrl_ImportCsv.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ImportCsv_Ctrl_ImportCsv.CTRL_ID = 'Admin_ImportCsv_Ctrl_ImportCsv';

      Admin_ImportCsv_Ctrl_ImportCsv.CTRL_AS = 'Ctrl';

      Admin_ImportCsv_Ctrl_ImportCsv.DEPS = [];

      Admin_ImportCsv_Ctrl_ImportCsv.prototype.init = function() {
        this.$scope.fileUploadOptions = {
          url: window.DP_BASE_API_URL + '/import_csv_upload'
        };
        this.$scope.fileUploadResults = null;
        this.$scope.fileSelected = false;
        this.$scope.importSettings = {
          fieldMappings: [],
          skipFirst: 1
        };
        this.setupUploadListeners();
      };

      /*
       	#
      */


      Admin_ImportCsv_Ctrl_ImportCsv.prototype.initialLoad = function() {};

      /*
      		#
      */


      Admin_ImportCsv_Ctrl_ImportCsv.prototype.setupUploadListeners = function() {
        var _this = this;
        this.$scope.$on('fileuploaddone', function(e, data) {
          _this.$scope.fileUploadResults = data.result;
          _this.$scope.fileSelected = false;
          if (_this.$scope.fileUploadResults.error) {
            return _this.$scope.fileUploadResults.upload_failed = true;
          }
        });
        this.$scope.$on('fileuploadfail', function(e, data) {
          _this.$scope.fileUploadResults = {};
          _this.$scope.fileUploadResults.upload_failed = true;
          return _this.$scope.fileSelected = false;
        });
        return this.$scope.$on('fileuploadchange', function(e, data) {
          return _this.$scope.fileSelected = true;
        });
      };

      /*
       	#
      */


      Admin_ImportCsv_Ctrl_ImportCsv.prototype.selectMapping = function(column_id, selected_field) {
        return alert(column_id + ' ' + selected_field);
      };

      return Admin_ImportCsv_Ctrl_ImportCsv;

    })(Admin_Ctrl_Base);
    return Admin_ImportCsv_Ctrl_ImportCsv.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ImportCsv.js.map
*/