(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Usersources/Helper/UsersourceTypeDecider'], function(Admin_Ctrl_Base, Admin_Usersources_Helper_UsersourceTypeDecider) {
    var Admin_Usersources_Ctrl_New;
    Admin_Usersources_Ctrl_New = (function(_super) {
      __extends(Admin_Usersources_Ctrl_New, _super);

      function Admin_Usersources_Ctrl_New() {
        return Admin_Usersources_Ctrl_New.__super__.constructor.apply(this, arguments);
      }

      Admin_Usersources_Ctrl_New.CTRL_ID = 'Admin_Usersources_Ctrl_New';

      Admin_Usersources_Ctrl_New.CTRL_AS = 'NewCtrl';

      Admin_Usersources_Ctrl_New.DEPS = ['$state'];

      Admin_Usersources_Ctrl_New.prototype.init = function() {
        this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
        return this.$scope.install_url = this.usersourceType === 'user' ? 'crm.usersources.install' : 'agents.usersources.install';
      };

      Admin_Usersources_Ctrl_New.prototype.initialLoad = function() {
        var url;
        url = '/usersources/available/app-packages/' + this.usersourceType;
        return this.Api.sendGet(url).then((function(_this) {
          return function(res) {
            return _this.packages = res.data;
          };
        })(this));
      };

      return Admin_Usersources_Ctrl_New;

    })(Admin_Ctrl_Base);
    return Admin_Usersources_Ctrl_New.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=New.js.map
