(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Usersources_Ctrl_New;
    Admin_Usersources_Ctrl_New = (function(_super) {
      __extends(Admin_Usersources_Ctrl_New, _super);

      function Admin_Usersources_Ctrl_New() {
        return Admin_Usersources_Ctrl_New.__super__.constructor.apply(this, arguments);
      }

      Admin_Usersources_Ctrl_New.CTRL_ID = 'Admin_Usersources_Ctrl_UsersourceNew';

      Admin_Usersources_Ctrl_New.CTRL_AS = 'EditCtrl';

      Admin_Usersources_Ctrl_New.DEPS = ['$stateParams'];

      Admin_Usersources_Ctrl_New.prototype.init = function() {
        this.usersourcesDataService = this.DataService.get('Usersources');
        this.usersourceType = this.$stateParams.usersource_type;
        return console.log("the type is: " + this.usersourceType);
      };

      return Admin_Usersources_Ctrl_New;

    })(Admin_Ctrl_Base);
    return Admin_Usersources_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=UsersourceNew.js.map
