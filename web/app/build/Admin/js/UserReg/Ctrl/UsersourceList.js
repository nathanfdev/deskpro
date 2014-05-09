(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserReg_Ctrl_UsersourceList;
    Admin_UserReg_Ctrl_UsersourceList = (function(_super) {
      __extends(Admin_UserReg_Ctrl_UsersourceList, _super);

      function Admin_UserReg_Ctrl_UsersourceList() {
        return Admin_UserReg_Ctrl_UsersourceList.__super__.constructor.apply(this, arguments);
      }

      Admin_UserReg_Ctrl_UsersourceList.CTRL_ID = 'Admin_UserReg_Ctrl_UsersourceList';

      Admin_UserReg_Ctrl_UsersourceList.CTRL_AS = 'ListCtrl';

      Admin_UserReg_Ctrl_UsersourceList.DEPS = [];

      Admin_UserReg_Ctrl_UsersourceList.prototype.init = function() {};

      Admin_UserReg_Ctrl_UsersourceList.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet('/apps?tags=usersources').then((function(_this) {
          return function(result) {
            return _this.apps = result.data.apps.filter(function(x) {
              return !x["package"].is_custom;
            });
          };
        })(this));
        return promise;
      };

      return Admin_UserReg_Ctrl_UsersourceList;

    })(Admin_Ctrl_Base);
    return Admin_UserReg_Ctrl_UsersourceList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=UsersourceList.js.map
