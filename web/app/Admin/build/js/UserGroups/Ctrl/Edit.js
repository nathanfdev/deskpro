(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserGroups_Ctrl_Edit, _ref;
    Admin_UserGroups_Ctrl_Edit = (function(_super) {
      __extends(Admin_UserGroups_Ctrl_Edit, _super);

      function Admin_UserGroups_Ctrl_Edit() {
        _ref = Admin_UserGroups_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_UserGroups_Ctrl_Edit.CTRL_ID = 'Admin_UserGroups_Ctrl_Edit';

      Admin_UserGroups_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_UserGroups_Ctrl_Edit.DEPS = [];

      Admin_UserGroups_Ctrl_Edit.prototype.init = function() {};

      return Admin_UserGroups_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/