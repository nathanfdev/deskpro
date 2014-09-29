(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerFileCheck_Ctrl_ServerFileCheck;
    Admin_ServerFileCheck_Ctrl_ServerFileCheck = (function(_super) {
      __extends(Admin_ServerFileCheck_Ctrl_ServerFileCheck, _super);

      function Admin_ServerFileCheck_Ctrl_ServerFileCheck() {
        return Admin_ServerFileCheck_Ctrl_ServerFileCheck.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.CTRL_ID = 'Admin_ServerFileCheck_Ctrl_ServerFileCheck';

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.CTRL_AS = 'Ctrl';

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.DEPS = [];

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.prototype.init = function() {
        this.server_file_check = null;
        this.total_checks = 0;
        this.current_check = 0;
        this.current_percentage = 0;
        this.check_started = false;
        this.check_in_progress = false;
        this.has_errors = false;
        this.show_log = false;
        this.show_log_text = 'Show Log';
        this.logs = [];
        return this.error_logs = [];
      };

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.prototype.initialLoad = function() {
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

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.prototype.startCheck = function() {
        this.current_check = 0;
        this.current_percentage = 0;
        this.check_started = true;
        this.check_in_progress = true;
        this.has_errors = false;
        this.logs = [];
        this.error_logs = [];
        return this.doNextRequest();
      };


      /*
      		 * Execute AJAX request to next batch of files
       */

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.prototype.doNextRequest = function() {
        this.current_check++;
        if (this.current_check <= this.total_checks) {
          return this.Api.sendGet('/server_file_check/' + (this.current_check - 1)).then((function(_this) {
            return function(res) {
              var data, file, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
              data = res.data.server_file_check;
              if (data.okay) {
                _this.logs.push('Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + data.okay.length + ' files verified');
              }
              if (data.added) {
                _ref = data.added;
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  file = _ref[_i];
                  _this.logs.push('Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + ' File added: ' + file);
                }
              }
              if (data.changed && data.changed.length) {
                _ref1 = data.changed;
                for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                  file = _ref1[_j];
                  _this.logs.push('Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + ' File changed: ' + file);
                  _this.error_logs.push('CHANGED: ' + file);
                  _this.has_errors = true;
                }
              }
              if (data.removed && data.removed.length) {
                _ref2 = data.removed;
                for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                  file = _ref2[_k];
                  _this.logs.push('Batch ' + _this.current_check + ' of ' + _this.total_checks + ': ' + ' Missing: ' + file);
                  _this.error_logs.push('MISSING: ' + file);
                  _this.has_errors = true;
                }
              }
              _this.current_percentage = Math.ceil(_this.current_check / _this.total_checks * 100);
              return _this.doNextRequest();
            };
          })(this));
        } else {
          this.check_in_progress = false;
          return this.current_percentage = 100;
        }
      };


      /*
      		 * Show / hide 'show log' button
       */

      Admin_ServerFileCheck_Ctrl_ServerFileCheck.prototype.toggleLog = function() {
        this.show_log = !this.show_log;
        return this.show_log_text = this.show_log ? 'Hide Log' : 'Show Log';
      };

      return Admin_ServerFileCheck_Ctrl_ServerFileCheck;

    })(Admin_Ctrl_Base);
    return Admin_ServerFileCheck_Ctrl_ServerFileCheck.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerFileCheck.js.map
