define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Settings_Ctrl_RegSettings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_RegSettings'
    @CTRL_AS   = 'Settings'
    @DEPS      = []

    init: ->
      @settings = null

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'settings': '/registration_settings'
      }).then( (res) =>
        @$scope.settings = res.data.settings.registration_settings
        @settings = angular.copy(@$scope.settings)
      )

      return @$q.all([data_promise])

    isDirtyState: ->
      if not @settings then return false
      if not angular.equals(@settings, @$scope.settings)
        return true
      else
        return false

    save: ->
      postData = {
        registration_settings: @$scope.settings
      }

      if postData.registration_settings.reg_enabled == "1" or postData.registration_settings.reg_enabled == 1 or postData.registration_settings.reg_enabled == true
        postData.registration_settings.reg_enabled = true
      else
        postData.registration_settings.reg_enabled = false

      @startSpinner('saving')
      promise = @Api.sendPostJson('/registration_settings', postData).success( =>
        @settings = angular.copy(@$scope.settings)

        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_Settings_Ctrl_RegSettings.EXPORT_CTRL()