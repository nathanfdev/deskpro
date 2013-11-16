(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerErrorLogs_Ctrl_ServerErrorLogs, _ref;
    Admin_ServerErrorLogs_Ctrl_ServerErrorLogs = (function(_super) {
      __extends(Admin_ServerErrorLogs_Ctrl_ServerErrorLogs, _super);

      function Admin_ServerErrorLogs_Ctrl_ServerErrorLogs() {
        _ref = Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.CTRL_ID = 'Admin_ServerErrorLogs_Ctrl_ServerErrorLogs';

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.CTRL_AS = 'ServerErrorLogs';

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.DEPS = [];

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.prototype.init = function() {
        this.$scope.server_error_logs = null;
        return this.$scope.logs_size = 0;
      };

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendGet('/server_error_logs').then(function(res) {
          _this.$scope.server_error_logs = res.data.server_error_logs;
          return _this.$scope.logs_size = _.size(_this.$scope.server_error_logs.logs);
        });
        return this.$q.all([data_promise]);
      };

      return Admin_ServerErrorLogs_Ctrl_ServerErrorLogs;

    })(Admin_Ctrl_Base);
    return Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ServerErrorLogs.js.map
*/