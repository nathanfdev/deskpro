(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Apps/Ctrl/EditInstance', 'Admin/Usersources/Helper/UsersourceTypeDecider'], function(Admin_Ctrl_Base, Admin_Apps_Ctrl_EditInstance, Admin_Usersources_Helper_UsersourceTypeDecider) {
    var Admin_Usersources_Ctrl_Edit;
    Admin_Usersources_Ctrl_Edit = (function(_super) {
      __extends(Admin_Usersources_Ctrl_Edit, _super);

      function Admin_Usersources_Ctrl_Edit() {
        return Admin_Usersources_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Usersources_Ctrl_Edit.CTRL_ID = 'Admin_Usersources_Ctrl_Edit';

      Admin_Usersources_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Usersources_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_Usersources_Ctrl_Edit.prototype.init = function() {
        this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
        this.usersourcesDataService = this.DataService.get('Usersources');
        return this.usersourceId = this.$stateParams.id;
      };

      Admin_Usersources_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet('/usersources/' + this.usersourceType + '/' + this.usersourceId).then((function(_this) {
          return function(result) {
            _this.usersource = result.data.usersource;
            return console.log(_this.usersource);
          };
        })(this));
        return promise;
      };

      return Admin_Usersources_Ctrl_Edit;

    })(Admin_Apps_Ctrl_EditInstance);
    return Admin_Usersources_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
