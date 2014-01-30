(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminUpgrade/Main/Ctrl/UpgradeBase', 'DeskPRO/Util/Strings'], function(UpgradeBase, Strings) {
    var AdminUpgrade_Main_Ctrl_UpgradeWatch, _ref;
    AdminUpgrade_Main_Ctrl_UpgradeWatch = (function(_super) {
      __extends(AdminUpgrade_Main_Ctrl_UpgradeWatch, _super);

      function AdminUpgrade_Main_Ctrl_UpgradeWatch() {
        _ref = AdminUpgrade_Main_Ctrl_UpgradeWatch.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      AdminUpgrade_Main_Ctrl_UpgradeWatch.CTRL_ID = 'AdminUpgrade_Main_Ctrl_UpgradeWatch';

      AdminUpgrade_Main_Ctrl_UpgradeWatch.CTRL_AS = 'Watch';

      AdminUpgrade_Main_Ctrl_UpgradeWatch.DEPS = ['$interval', '$http'];

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.init = function() {
        var _this = this;
        window.WATCH = this;
        this.startTime = null;
        this.initialPollTime = false;
        this.$scope.step = {
          waiting: {
            on: true,
            complete: false
          },
          started: {
            on: false,
            complete: false
          },
          checks: {
            on: false,
            complete: false
          },
          download: {
            on: false,
            complete: false
          },
          backup_files: {
            on: false,
            complete: false
          },
          disable_helpdesk: {
            on: false,
            complete: false
          },
          backup_database: {
            on: false,
            complete: false
          },
          install_files: {
            on: false,
            complete: false
          },
          install_database: {
            on: false,
            complete: false
          },
          enable_helpdesk: {
            on: false,
            complete: false
          },
          done: {
            on: false,
            complete: false
          }
        };
        this.stepId = 0;
        this.steps = ['waiting', 'started', 'checks', 'download', 'backup_files', 'disable_helpdesk', 'backup_database', 'install_files', 'install_database', 'enable_helpdesk', 'done'];
        this.Api.sendDataGet({
          updateStatus: '/server/updates/auto'
        }).then(function(result) {
          if (!result.data.updateStatus.is_scheduled) {
            _this.$location.path('/');
            return;
          }
          _this.startTime = parseInt(result.data.updateStatus.start_time);
          _this.pollRunning = null;
          return _this.pollTimer = _this.$interval(function() {
            if (!_this.$scope.step.waiting.complete) {
              return _this.pollCheckStarted();
            } else {
              return _this.pollStatus();
            }
          }, 2000);
        });
      };

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.finishStep = function(stepName) {
        var i, id, name, _i;
        id = this.steps.indexOf(stepName);
        for (i = _i = 0; 0 <= id ? _i <= id : _i >= id; i = 0 <= id ? ++_i : --_i) {
          name = this.steps[i];
          this.$scope.step[name].on = false;
          this.$scope.step[name].complete = true;
        }
        name = this.steps[id + 1];
        return this.$scope.step[name].on = true;
      };

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.beginStep = function(stepName) {
        var i, id, name, _i, _ref1;
        id = this.steps.indexOf(stepName);
        for (i = _i = 0, _ref1 = id - 1; 0 <= _ref1 ? _i <= _ref1 : _i >= _ref1; i = 0 <= _ref1 ? ++_i : --_i) {
          name = this.steps[i];
          this.$scope.step[name].on = false;
          this.$scope.step[name].complete = true;
        }
        name = this.steps[id];
        return this.$scope.step[name].on = true;
      };

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.finishAll = function() {
        var name, _i, _len, _ref1;
        _ref1 = this.steps;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          name = _ref1[_i];
          this.$scope.step[name].on = false;
          this.$scope.step[name].complete = true;
        }
        this.$scope.step.done.on = true;
        return this.$scope.step.done.complete = false;
      };

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.pollCheckStarted = function() {
        var _this = this;
        if (this.pollRunning) {
          return null;
        }
        return this.pollRunning = this.Api.sendDataGet({
          updateStatus: '/server/updates/auto'
        }).then(function(result) {
          _this.pollRunning = null;
          if (result.data.updateStatus.with_perm_error) {
            return _this.handleError('error_write_perm');
          } else if (result.data.updateStatus.is_started) {
            return _this.finishStep('waiting');
          }
        }, function() {
          return _this.pollRunning = null;
        });
      };

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.pollStatus = function() {
        var url,
          _this = this;
        if (this.pollRunning) {
          return null;
        }
        url = DP_BASE_URL;
        url = url.replace(/\/index\.php\//, '/');
        url += 'auto-update-status.php';
        return this.pollRunning = this.$http({
          method: 'GET',
          url: url,
          cache: false,
          responseType: 'text'
        }).success(function(content) {
          var last, last_time, lines, m, restart_timer, time, _i, _len;
          content = Strings.trim(content);
          lines = content.split(/\n+/);
          if (content.length === 0) {
            return;
          }
          last_time = _this.initialPollTime || _this.startTime;
          restart_timer = true;
          for (_i = 0, _len = lines.length; _i < _len; _i++) {
            last = lines[_i];
            m = /^STATUS\((.*?)\)@([0-9\.]+)#(.*?)$/.exec(last);
            if (m) {
              time = parseFloat(m[2]);
              if (time >= last_time) {
                updateStatus(m[1], m[3], m[2]);
              }
              if (m[1] === 'done' || m[1].indexOf('error_') === 0) {
                restart_timer = false;
              }
            }
          }
          if (!restart_timer) {
            return _this.$interval.cancel(_this.pollTimer);
          }
        }).then(function() {
          return _this.pollRunning = null;
        }, function() {
          return _this.pollRunning = null;
        });
      };

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.updateStatus = function(code, message, time) {
        if (code.indexOf('error_') === 0) {
          this.handleError(code, message);
          return;
        }
        switch (code) {
          case 'runner_start':
            return this.finishStep('waiting');
          case 'start':
            return this.finishStep('waiting');
          case 'basic_checks_start':
            return this.beginStep('checks');
          case 'helpdesk_offline':
            return this.beginStep('disable_helpdesk');
          case 'helpdesk_online':
            return this.beginStep('enable_helpdesk');
          case 'file_backup_start':
            return this.beginStep('backup_files');
          case 'file_backup_copy_start':
            return this.beginStep('backup_files');
          case 'file_backup_zip_start':
            return this.beginStep('backup_files');
          case 'file_backup_cleanup_start':
            return this.beginStep('backup_files');
          case 'database_backup_start':
            return this.beginStep('backup_database');
          case 'downloading_update_start':
            return this.beginStep('download');
          case 'installing_files_start':
            return this.beginStep('install_files');
          case 'updating_db_start':
            return this.beginStep('install_database');
          case 'updating_db_start':
            return this.beginStep('install_database');
          case 'file_backup_loc':
            return this.$scope.file_backup_loc = message;
          case 'database_backup_loc':
            return this.$scope.db_backup_loc = message;
          case 'done':
            return this.finishAll();
        }
      };

      AdminUpgrade_Main_Ctrl_UpgradeWatch.prototype.handleError = function(code, message) {
        var code_short;
        this.$scope.is_error = true;
        this.$scope.error = {};
        code_short = code.replace(/^error_/, '');
        this.$scope.error[code_short] = true;
        return this.$scope.error_message = message;
      };

      return AdminUpgrade_Main_Ctrl_UpgradeWatch;

    })(UpgradeBase);
    return AdminUpgrade_Main_Ctrl_UpgradeWatch.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=UpgradeWatch.js.map
*/