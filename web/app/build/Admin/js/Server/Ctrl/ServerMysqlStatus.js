(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus;
    Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus = (function(_super) {
      __extends(Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus, _super);

      function Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus() {
        return Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.CTRL_ID = 'Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus';

      Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.CTRL_AS = 'ServerMysqlStatus';

      Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.DEPS = [];

      Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.prototype.init = function() {
        return this.$scope.server_mysql_status = null;
      };

      Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_mysql_status').then((function(_this) {
          return function(res) {
            return _this.$scope.server_mysql_status = res.data.server_mysql_status;
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      return Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus;

    })(Admin_Ctrl_Base);
    return Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerMysqlStatus.js.map
