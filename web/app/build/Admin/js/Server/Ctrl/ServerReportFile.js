(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerReportFile_Ctrl_ServerReportFile;
    Admin_ServerReportFile_Ctrl_ServerReportFile = (function(_super) {
      __extends(Admin_ServerReportFile_Ctrl_ServerReportFile, _super);

      function Admin_ServerReportFile_Ctrl_ServerReportFile() {
        return Admin_ServerReportFile_Ctrl_ServerReportFile.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerReportFile_Ctrl_ServerReportFile.CTRL_ID = 'Admin_ServerReportFile_Ctrl_ServerReportFile';

      Admin_ServerReportFile_Ctrl_ServerReportFile.CTRL_AS = 'Ctrl';

      Admin_ServerReportFile_Ctrl_ServerReportFile.DEPS = ['$window'];

      Admin_ServerReportFile_Ctrl_ServerReportFile.prototype.init = function() {
        this.server_file_check = null;
        this.total_checks = 0;
        this.current_check = 0;
        this.current_percentage = 0;
        this.check_started = false;
        this.check_in_progress = false;
        this.file_check_results = '';
        this.server_file_check_done = false;
        return this.$scope.with_file_check = true;
      };

      Admin_ServerReportFile_Ctrl_ServerReportFile.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_file_check').then((function(_this) {
          return function(res) {
            _this.server_file_check = res.data.server_file_check;
            _this.total_checks = _this.server_file_check.count;
            if (_this.total_checks === 1) {
              _this.current_check = -1;
              return _this.doNextRequest();
            }
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
      		 * Starting the process of integrity file check
       */

      Admin_ServerReportFile_Ctrl_ServerReportFile.prototype.startCheck = function() {
        this.current_check = 0;
        this.current_percentage = 0;
        this.check_started = true;
        this.check_in_progress = true;
        this.file_check_results = '';
        return this.doNextRequest();
      };


      /*
      		 * Execute AJAX request to next batch of files
       */

      Admin_ServerReportFile_Ctrl_ServerReportFile.prototype.doNextRequest = function() {
        this.current_check++;
        if (this.current_check < this.total_checks && this.$scope.with_file_check && this.file_check_results.length < 153600) {
          return this.Api.sendGet('/server_file_check/' + this.current_check).then((function(_this) {
            return function(res) {
              var data, file, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
              data = res.data.server_file_check;
              if (data.okay) {
                _this.file_check_results += 'Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + data.okay.length + ' files verified' + "\n";
              }
              if (data.added) {
                _ref = data.added;
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  file = _ref[_i];
                  _this.file_check_results += 'Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + ' File added: ' + file + "\n";
                }
              }
              if (data.changed && data.changed.length) {
                _ref1 = data.changed;
                for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                  file = _ref1[_j];
                  _this.file_check_results += 'Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + ' File changed: ' + file + "\n";
                }
              }
              if (data.removed && data.removed.length) {
                _ref2 = data.removed;
                for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                  file = _ref2[_k];
                  _this.file_check_results += 'Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + ' Missing: ' + file + "\n";
                }
              }
              _this.current_percentage = Math.ceil(_this.current_check / _this.total_checks * 100);
              return _this.doNextRequest();
            };
          })(this));
        } else {
          if (this.file_check_results >= 153600) {
            this.file_check_results = this.file_check_results.substring(0, 153600) + "\n\n(Too many changes detected, results truncated)";
          }
          this.check_in_progress = false;
          this.current_percentage = 100;
          this.server_file_check_done = true;
          return this.redirectToReportFile();
        }
      };


      /*
      		 * After we've donw with file integrity checking we coudl redirect user to actual report file
       */

      Admin_ServerReportFile_Ctrl_ServerReportFile.prototype.redirectToReportFile = function() {
        return this.Api.sendPost('/server_report_file/file_check_results', {
          file_check_results: this.file_check_results
        }).then((function(_this) {
          return function(res) {
            return _this.$scope.download_link = window.DP_BASE_API_URL + '/server_report_file?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN;
          };
        })(this));
      };

      return Admin_ServerReportFile_Ctrl_ServerReportFile;

    })(Admin_Ctrl_Base);
    return Admin_ServerReportFile_Ctrl_ServerReportFile.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerReportFile.js.map
