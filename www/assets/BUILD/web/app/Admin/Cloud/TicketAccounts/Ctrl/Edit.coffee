define [
  'Admin/Cloud/TicketAccounts/FormModel/EditTicketAccountModel',
  'Admin/TicketAccounts/Ctrl/Edit',
], (
  EditTicketAccountModel,
  BaseEdit
) ->
  class Admin_Cloud_TicketAccounts_Ctrl_Edit extends BaseEdit
    @CTRL_ID = 'Admin_Cloud_TicketAccounts_Ctrl_Edit'
    @CTRL_AS = 'TicketAccountsEdit'

    init: ->
      super()

      # dont show the new account warning,
      # doesnt apply for cloud because we arent connecting to existing mailboxes
      @new_is_confirmed = true

    getFormModel: ->
      return new EditTicketAccountModel(@account || {}, @deps || [], @trigger || {}, @brands || {})

    setupTestModalScope: ($scope) ->
      if @form_model.form.use_custom_email_address
        $scope.test_email.from = @form_model.form.custom_email_address
      else
        $scope.test_email.from = @form_model.form.address

      $scope.test_email.from_is_fixed = true

  Admin_Cloud_TicketAccounts_Ctrl_Edit.EXPORT_CTRL()
