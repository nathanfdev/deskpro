(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Labels/Base/Ctrl/List'], function(Admin_Labels_Base_Ctrl_List) {
    var Admin_Labels_Person_Ctrl_List;
    Admin_Labels_Person_Ctrl_List = (function(_super) {
      __extends(Admin_Labels_Person_Ctrl_List, _super);

      function Admin_Labels_Person_Ctrl_List() {
        return Admin_Labels_Person_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_Labels_Person_Ctrl_List.CTRL_ID = 'Admin_Labels_Person_Ctrl_List';

      Admin_Labels_Person_Ctrl_List.DEPS = ['em', '$rootScope', 'LabelManager'];

      Admin_Labels_Person_Ctrl_List.CTRL_AS = 'LabelsList';

      Admin_Labels_Person_Ctrl_List.prototype.init = function() {
        Admin_Labels_Person_Ctrl_List.__super__.init.call(this);
        this.api_endpoint = '/person_labels';
        this.ng_route = 'crm.user_labels';
        return this.typename = 'labels_people';
      };

      return Admin_Labels_Person_Ctrl_List;

    })(Admin_Labels_Base_Ctrl_List);
    return Admin_Labels_Person_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
