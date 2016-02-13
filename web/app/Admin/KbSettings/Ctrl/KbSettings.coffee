define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_KbSettings_Ctrl_KbSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_KbSettings_Ctrl_KbSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS    = []

    initialLoad: ->
      @Api.sendGet('/settings/portal/kb').then( (res) =>
        @$scope.settings = res.data.settings
      )

    save: ->
      postData = {
        settings: @$scope.settings
      }

      @startSpinner('saving')
      @Api.sendPostJson('/settings/portal/kb', postData).then( =>
        @stopSpinner('saving')
      )


  Admin_KbSettings_Ctrl_KbSettings.EXPORT_CTRL()