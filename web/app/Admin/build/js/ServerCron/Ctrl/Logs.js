(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerCron_Ctrl_Logs, _ref;
    Admin_ServerCron_Ctrl_Logs = (function(_super) {
      __extends(Admin_ServerCron_Ctrl_Logs, _super);

      function Admin_ServerCron_Ctrl_Logs() {
        _ref = Admin_ServerCron_Ctrl_Logs.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ServerCron_Ctrl_Logs.CTRL_ID = 'Admin_ServerCron_Ctrl_Logs';

      Admin_ServerCron_Ctrl_Logs.CTRL_AS = 'LogsCtrl';

      Admin_ServerCron_Ctrl_Logs.DEPS = [];

      Admin_ServerCron_Ctrl_Logs.prototype.init = function() {
        this.server_cron_logs = null;
        return this.filter = {
          job_id: '',
          priority: ''
        };
      };

      Admin_ServerCron_Ctrl_Logs.prototype.initialLoad = function() {
        return this.loadResults();
      };

      Admin_ServerCron_Ctrl_Logs.prototype.loadResults = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendGet('/server_cron/logs', {
          job_id: this.filter.job_id,
          priority: this.filter.priority
        }).then(function(res) {
          return _this.server_cron_logs = res.data.server_cron_logs;
        });
        return this.$q.all([data_promise]);
      };

      Admin_ServerCron_Ctrl_Logs.prototype.updateFilter = function() {
        return this.loadResults();
      };

      return Admin_ServerCron_Ctrl_Logs;

    })(Admin_Ctrl_Base);
    return Admin_ServerCron_Ctrl_Logs.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Logs.js.map
*/