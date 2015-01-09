define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_Setup extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_Setup'
    @CTRL_AS = 'Ctrl'

    init: ->
      @settings = {}

    initialLoad: ->
      promise = @Api.sendGet('/settings/portal/general').then( (result) =>
        @settings = result.data.portal_settings
      )
      return promise

    saveSettings: ->
      postData = {
        portal_settings: @settings
      }

      @startSpinner()
      @Apo.sendPost('/settings/portal/general', postData).then(=>
        @stopSpinner()
      )

  Admin_Portal_Ctrl_Setup.EXPORT_CTRL()