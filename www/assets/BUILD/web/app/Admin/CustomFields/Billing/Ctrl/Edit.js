define [
  'Admin/CustomFields/Base/Ctrl/Edit',
], (
  Admin_CustomFields_Base_Ctrl_Edit
) ->
  class Admin_CustomFields_Billing_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit
    @CTRL_ID = 'Admin_CustomFields_Billing_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'
    @DEPS    = []

    getDataService: ->
      return @DataService.get('BillingFields')

    getBaseRouteName: ->
      return 'tickets.timelog_billing_fields'

  Admin_CustomFields_Billing_Ctrl_Edit.EXPORT_CTRL()