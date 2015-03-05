define [
  'Admin/CustomFields/Base/Ctrl/Edit',
], (
  Admin_CustomFields_Base_Ctrl_Edit
) ->
  class Admin_CustomFields_User_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit
    @CTRL_ID = 'Admin_CustomFields_User_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'
    @DEPS    = []

    initialLoadExtra: ->
      promise = @Api.sendGet('/apps?tags=usersources').then( (result) =>
        @us_apps = result.data.apps.filter((x) -> !x.package.is_custom)
      )
      return promise

    getDataService: ->
      return @DataService.get('UserFields')

    getBaseRouteName: ->
      return "crm.user_fields"

  Admin_CustomFields_User_Ctrl_Edit.EXPORT_CTRL()