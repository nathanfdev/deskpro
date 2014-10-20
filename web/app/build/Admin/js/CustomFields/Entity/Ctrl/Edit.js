(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/CustomFields/Base/Ctrl/Edit'], function(Admin_CustomFields_Base_Ctrl_Edit) {
    var Admin_CustomFields_Entity_Ctrl_Edit;
    Admin_CustomFields_Entity_Ctrl_Edit = (function(_super) {
      __extends(Admin_CustomFields_Entity_Ctrl_Edit, _super);

      function Admin_CustomFields_Entity_Ctrl_Edit() {
        return Admin_CustomFields_Entity_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_CustomFields_Entity_Ctrl_Edit.CTRL_ID = 'Admin_CustomFields_Entity_Ctrl_Edit';

      Admin_CustomFields_Entity_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_CustomFields_Entity_Ctrl_Edit.DEPS = [];

      Admin_CustomFields_Entity_Ctrl_Edit.prototype.getDataService = function() {
        return this.DataService.get('EntityFields');
      };

      Admin_CustomFields_Entity_Ctrl_Edit.prototype.getBaseRouteName = function() {
        return "crm.user_fields.specific";
      };

      return Admin_CustomFields_Entity_Ctrl_Edit;

    })(Admin_CustomFields_Base_Ctrl_Edit);
    return Admin_CustomFields_Entity_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
