(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Cloud/TicketAccounts/FormModel/EditTicketAccountModel', 'Admin/TicketAccounts/Ctrl/Edit'], function(EditTicketAccountModel, BaseEdit) {
    var Admin_Cloud_TicketAccounts_Ctrl_Edit;
    Admin_Cloud_TicketAccounts_Ctrl_Edit = (function(_super) {
      __extends(Admin_Cloud_TicketAccounts_Ctrl_Edit, _super);

      function Admin_Cloud_TicketAccounts_Ctrl_Edit() {
        return Admin_Cloud_TicketAccounts_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Cloud_TicketAccounts_Ctrl_Edit.CTRL_ID = 'Admin_Cloud_TicketAccounts_Ctrl_Edit';

      Admin_Cloud_TicketAccounts_Ctrl_Edit.CTRL_AS = 'TicketAccountsEdit';

      Admin_Cloud_TicketAccounts_Ctrl_Edit.prototype.init = function() {
        Admin_Cloud_TicketAccounts_Ctrl_Edit.__super__.init.call(this);
        return this.new_is_confirmed = true;
      };

      Admin_Cloud_TicketAccounts_Ctrl_Edit.prototype.getFormModel = function() {
        return new EditTicketAccountModel(this.account || {}, this.deps || [], this.trigger || {});
      };

      return Admin_Cloud_TicketAccounts_Ctrl_Edit;

    })(BaseEdit);
    return Admin_Cloud_TicketAccounts_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
