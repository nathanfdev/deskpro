define [
  'Admin/CustomFields/Base/Ctrl/Edit',
], (
  Admin_CustomFields_Base_Ctrl_Edit
) ->
  class Admin_CustomFields_Kb_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit
    @CTRL_ID = 'Admin_CustomFields_Kb_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'
    @DEPS    = []

    getDataService: ->
      return @DataService.get('KbFields')

    getBaseRouteName: ->
      return "portal.kb_custom_fields"

    type: ->
      'kb'

  Admin_CustomFields_Kb_Ctrl_Edit.EXPORT_CTRL()
