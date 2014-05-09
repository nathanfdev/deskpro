(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerErrorLogs_Ctrl_View;
    Admin_ServerErrorLogs_Ctrl_View = (function(_super) {
      __extends(Admin_ServerErrorLogs_Ctrl_View, _super);

      function Admin_ServerErrorLogs_Ctrl_View() {
        return Admin_ServerErrorLogs_Ctrl_View.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerErrorLogs_Ctrl_View.CTRL_ID = 'Admin_ServerErrorLogs_Ctrl_View';

      Admin_ServerErrorLogs_Ctrl_View.CTRL_AS = 'ServerErrorLogsView';

      Admin_ServerErrorLogs_Ctrl_View.DEPS = [];

      Admin_ServerErrorLogs_Ctrl_View.prototype.init = function() {
        return this.error_log = {};
      };

      Admin_ServerErrorLogs_Ctrl_View.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_error_logs/' + this.$stateParams.id).then((function(_this) {
          return function(res) {
            return _this.error_log = res.data.server_error_log;
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      return Admin_ServerErrorLogs_Ctrl_View;

    })(Admin_Ctrl_Base);
    return Admin_ServerErrorLogs_Ctrl_View.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerErrorLogsView.js.map
