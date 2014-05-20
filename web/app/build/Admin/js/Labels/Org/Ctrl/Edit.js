(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Labels/Base/Ctrl/Edit'], function(Admin_Labels_Base_Ctrl_Edit) {
    var Admin_Labels_Org_Ctrl_Edit;
    Admin_Labels_Org_Ctrl_Edit = (function(_super) {
      __extends(Admin_Labels_Org_Ctrl_Edit, _super);

      function Admin_Labels_Org_Ctrl_Edit() {
        return Admin_Labels_Org_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Labels_Org_Ctrl_Edit.CTRL_ID = 'Admin_Labels_Org_Ctrl_Edit';

      Admin_Labels_Org_Ctrl_Edit.CTRL_AS = 'LabelsEdit';

      Admin_Labels_Org_Ctrl_Edit.DEPS = ['em', '$stateParams', '$rootScope', 'LabelManager'];

      Admin_Labels_Org_Ctrl_Edit.prototype.init = function() {
        Admin_Labels_Org_Ctrl_Edit.__super__.init.call(this);
        this.api_endpoint = '/org_labels';
        this.ng_route = 'crm.org_labels';
        return this.typename = 'labels_organizations';
      };

      return Admin_Labels_Org_Ctrl_Edit;

    })(Admin_Labels_Base_Ctrl_Edit);
    return Admin_Labels_Org_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
