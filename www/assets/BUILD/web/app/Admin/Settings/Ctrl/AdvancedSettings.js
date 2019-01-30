define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Settings_Ctrl_AdvancedSettings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_AdvancedSettings'
    @CTRL_AS   = 'Settings'
    @DEPS      = []

    init: ->
      @settings = null

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'settings': '/all_settings_raw'
      }).then( (res) =>
        @$scope.settings = res.data.settings.all_settings
      )

      return @$q.all([data_promise])

    save: ->
      postData = {
        all_settings: {}
      }

      for setting in @$scope.settings
        postData.all_settings[setting.name] = setting.value

      @startSpinner('saving')
      promise = @Api.sendPostJson('/all_settings_raw', postData).success( =>
        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_Settings_Ctrl_AdvancedSettings.EXPORT_CTRL()