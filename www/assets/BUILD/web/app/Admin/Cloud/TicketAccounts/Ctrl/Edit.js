define([
  'Admin/Cloud/TicketAccounts/FormModel/EditTicketAccountModel',
  'Admin/TicketAccounts/Ctrl/Edit',
], function(
  EditTicketAccountModel,
  BaseEdit
) {
  class Admin_Cloud_TicketAccounts_Ctrl_Edit extends BaseEdit {
    static initClass() {
      this.CTRL_ID = 'Admin_Cloud_TicketAccounts_Ctrl_Edit';
      this.CTRL_AS = 'TicketAccountsEdit';
    }

    init() {
      super.init();

      // dont show the new account warning,
      // doesnt apply for cloud because we arent connecting to existing mailboxes
      return this.new_is_confirmed = true;
    }

    getFormModel() {
      return new EditTicketAccountModel(this.account || {}, this.deps || [], this.trigger || {}, this.brands || {});
    }

    setupTestModalScope($scope) {
      if (this.form_model.form.use_custom_email_address) {
        $scope.test_email.from = this.form_model.form.custom_email_address;
      } else {
        $scope.test_email.from = this.form_model.form.address;
      }

      return $scope.test_email.from_is_fixed = true;
    }
  }
  Admin_Cloud_TicketAccounts_Ctrl_Edit.initClass();

  return Admin_Cloud_TicketAccounts_Ctrl_Edit.EXPORT_CTRL();
});
