(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo;
    Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo = (function(_super) {
      __extends(Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo, _super);

      function Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo() {
        return Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.CTRL_ID = 'Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo';

      Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.CTRL_AS = 'ServerMysqlInfo';

      Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.DEPS = [];

      Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.prototype.init = function() {
        return this.$scope.is_loading_schemadiff = true;
      };

      Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.prototype.initialLoad = function() {
        this.Api.sendGet('/server_mysql_info/schema-diff').then((function(_this) {
          return function(res) {
            _this.$scope.schema_diff = res.data.mysql_schema_diff;
            return _this.$scope.is_loading_schemadiff = false;
          };
        })(this));
        return this.Api.sendGet('/server_mysql_info').then((function(_this) {
          return function(res) {
            return _this.$scope.server_mysql_info = res.data.server_mysql_info;
          };
        })(this));
      };

      return Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo;

    })(Admin_Ctrl_Base);
    return Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerMysqlInfo.js.map
