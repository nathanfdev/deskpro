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

      Admin_ImportCsv_Ctrl_ImportCsv.DEPS = ['Api', 'Growl'];

      Admin_ImportCsv_Ctrl_ImportCsv.prototype.init = function() {
        var key, _i, _len, _ref1;
        this.$scope.fileUploadOptions = {
          url: window.DP_BASE_API_URL + '/import_csv_upload'
        };
        this.$scope.fileUploadResults = null;
        this.$scope.fileSelected = false;
        this.$scope.importSettings = {
          fieldMappings: [],
          additionalMappings: [],
          skipFirst: 1,
          showExtraMappings: {}
        };
        this.showExtraMappingsCases = ['organization', 'phone', 'website', 'im', 'twitter', 'linkedin', 'facebook', 'address1', 'address2', 'city', 'state', 'post_code', 'country', 'new_custom'];
        _ref1 = this.showExtraMappingsCases;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          key = _ref1[_i];
          this.$scope.importSettings.showExtraMappings[key] = [];
        }
        this.setupUploadListeners();
      };

      /*
      		#
      */


      Admin_ImportCsv_Ctrl_ImportCsv.prototype.setupUploadListeners = function() {
        var _this = this;
        this.$scope.$on('fileuploaddone', function(e, data) {
          var idx, key, _i, _len, _ref1, _results;
          _this.$scope.fileUploadResults = data.result;
          _this.$scope.fileSelected = false;
          if (_this.$scope.fileUploadResults.error) {
            _this.$scope.fileUploadResults.upload_failed = true;
          }
          if (!_this.$scope.fileUploadResults.upload_failed) {
            _ref1 = _this.$scope.fileUploadResults.columns;
            _results = [];
            for (idx = _i = 0, _len = _ref1.length; _i < _len; idx = ++_i) {
              key = _ref1[idx];
              _results.push(_this.$scope.importSettings.additionalMappings[idx] = {});
            }
            return _results;
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
       	# Sends requests to launch a task for starting CSV import
      */


      Admin_ImportCsv_Ctrl_ImportCsv.prototype.startImport = function() {
        var field_maps, filename, key, key2, obj, skip_first, user_filename, value, value2, _i, _len, _ref1, _ref2,
          _this = this;
        field_maps = [];
        _ref1 = this.$scope.importSettings.fieldMappings;
        for (key = _i = 0, _len = _ref1.length; _i < _len; key = ++_i) {
          value = _ref1[key];
          obj = {
            map: value
          };
          _ref2 = this.$scope.importSettings.additionalMappings[key];
          for (key2 in _ref2) {
            value2 = _ref2[key2];
            obj[key2] = value2;
          }
          field_maps.push(obj);
        }
        user_filename = this.$scope.fileUploadResults.user_filename;
        skip_first = this.$scope.importSettings.skipFirst;
        filename = this.$scope.fileUploadResults.filename;
        return this.Api.sendPostJson('import_csv_import', {
          field_maps: field_maps,
          user_filename: user_filename,
          skip_first: skip_first,
          filename: filename
        }).then(function(result) {});
      };

      /*
       	# Handler for selection of field mapping
       	# Shows / hides appropriate extra mapping for mappings table, could add extra functionality here later
       	#
       	# @param {Integer} column_id - id of column from the table with mapping
       	# @param {String} selected_field - name of field sent by 'ng-change'
      */


      Admin_ImportCsv_Ctrl_ImportCsv.prototype.selectMapping = function(column_id, selected_field) {
        var key;
        for (key in this.$scope.importSettings.showExtraMappings) {
          this.$scope.importSettings.showExtraMappings[key][column_id] = false;
        }
        if (this.showExtraMappingsCases.indexOf(selected_field) > -1) {
          return this.$scope.importSettings.showExtraMappings[selected_field][column_id] = true;
        }
      };

      return Admin_ImportCsv_Ctrl_ImportCsv;

    })(Admin_Ctrl_Base);
    return Admin_ImportCsv_Ctrl_ImportCsv.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ImportCsv.js.map
*/