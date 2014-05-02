(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
    var AdminStart_Ctrl_Cron;
    AdminStart_Ctrl_Cron = (function(_super) {
      __extends(AdminStart_Ctrl_Cron, _super);

      function AdminStart_Ctrl_Cron() {
        return AdminStart_Ctrl_Cron.__super__.constructor.apply(this, arguments);
      }

      AdminStart_Ctrl_Cron.CTRL_ID = 'AdminStart_Ctrl_Cron';

      AdminStart_Ctrl_Cron.DEPS = ['$location', '$timeout'];

      AdminStart_Ctrl_Cron.prototype.init = function() {};

      AdminStart_Ctrl_Cron.prototype.startWaiting = function() {
        this.$scope.is_waiting = true;
        this.start_time = (new Date()).getTime() / 1000;
        this.$scope.boot_errors = null;
        this.$scope.error_type = null;
        return this.pollWaiting();
      };

      AdminStart_Ctrl_Cron.prototype.pollWaiting = function() {
        return this.getCronStatus().success((function(_this) {
          return function(data) {
            var now;
            if (data.last_run) {
              return _this.$location.path('/email');
            } else {
              if (data.cron_boot_errors) {
                _this.$scope.is_waiting = false;
                _this.$scope.error_type = 'boot';
                return _this.$scope.boot_errors = data.cron_boot_errors;
              } else {
                now = (new Date()).getTime() / 1000;
                if (now - _this.start_time > 66) {
                  _this.$scope.is_waiting = false;
                  return _this.$scope.error_type = 'timeout';
                } else {
                  return _this.$timeout(function() {
                    return _this.pollWaiting();
                  }, 2000);
                }
              }
            }
          };
        })(this)).error((function(_this) {
          return function() {
            if (_this.$scope.is_waiting) {
              return _this.$timeout(function() {
                return _this.pollWaiting();
              }, 2000);
            }
          };
        })(this));
      };

      AdminStart_Ctrl_Cron.prototype.getCronStatus = function() {
        return this.Api.sendGet('/server/cron-status');
      };

      return AdminStart_Ctrl_Cron;

    })(StartBase);
    return AdminStart_Ctrl_Cron.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Cron.js.map
