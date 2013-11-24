(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_ServerPhpInfo_Ctrl_ServerPhpInfo, _ref;
    Admin_ServerPhpInfo_Ctrl_ServerPhpInfo = (function(_super) {
      __extends(Admin_ServerPhpInfo_Ctrl_ServerPhpInfo, _super);

      function Admin_ServerPhpInfo_Ctrl_ServerPhpInfo() {
        _ref = Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.CTRL_ID = 'Admin_ServerPhpInfo_Ctrl_ServerPhpInfo';

      Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.CTRL_AS = 'ServerPhpInfo';

      Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.DEPS = [];

      Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.prototype.init = function() {};

      Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendGet('/server_php_info').then(function(res) {
          return _this.$scope.server_php_info = res.data.server_php_info;
        });
        return this.$q.all([data_promise]);
      };

      return Admin_ServerPhpInfo_Ctrl_ServerPhpInfo;

    })(Admin_Ctrl_Base);
    return Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ServerPhpInfo.js.map
*/