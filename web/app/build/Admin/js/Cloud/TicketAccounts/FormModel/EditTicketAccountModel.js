(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Util', 'Admin/TicketAccounts/FormModel/EditTicketAccountModel'], function(Util, BaseFormModel) {
    var Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel;
    return Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel = (function(_super) {
      __extends(Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel, _super);

      function Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel(account, deps, trigger) {
        this.account = account;
        Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel.__super__.constructor.call(this, this.account, deps, trigger);
        if (this.form.address && this.form.address.indexOf('@') !== -1) {
          this.form.address_name = this.form.address.split('@')[0];
        }
        this.form.use_custom_email_address = false;
        if (this.account.other_addresses && this.account.other_addresses.length && this.account.options.custom_email_address) {
          this.form.custom_email_address = this.account.other_addresses.shift();
          this.form.use_custom_email_address = true;
        }
        if (this.account.other_addresses && this.account.other_addresses.length) {
          this.form.with_email_aliases = true;
          this.form.other_addresses = this.account.other_addresses.join(', ');
        } else {
          this.form.with_email_aliases = false;
          this.form.other_addresses = '';
        }
      }

      Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel.prototype.getFormData = function() {
        var form;
        form = Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel.__super__.getFormData.call(this);
        if (!form.with_email_aliases) {
          form.other_addresses = '';
        }
        return form;
      };

      Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel.prototype.apply = function() {
        this.account.address = this.form.address;
        if (this.form.use_custom_email_address && this.form.custom_email_address) {
          if (this.account.options == null) {
            this.account.options = {};
          }
          return this.account.options.custom_email_address = this.form.custom_email_address;
        }
      };

      return Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel;

    })(BaseFormModel);
  });

}).call(this);

//# sourceMappingURL=EditTicketAccountModel.js.map
