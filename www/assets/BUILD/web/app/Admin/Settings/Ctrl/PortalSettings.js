define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Settings_Ctrl_PortalSettings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_PortalSettings'
    @CTRL_AS   = 'Settings'
    @DEPS      = []

    init: ->
      @settings = null

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'settings': '/portal_settings'
      }).then( (res) =>
        @$scope.settings = res.data.settings.portal_settings
        @$scope.show_ratings_opt = false
        if @$scope.settings.show_ratings > 0
          @$scope.show_ratings_opt = true
        else
          @$scope.settings.show_ratings = 1

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

      if not @$scope.show_ratings_opt
        @$scope.settings.show_ratings = 0

      postData = {
        portal_settings: @$scope.settings
      }

      @startSpinner('saving')
      promise = @Api.sendPostJson('/portal_settings', postData).success( =>
        @settings = angular.copy(@$scope.settings)

        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_Settings_Ctrl_PortalSettings.EXPORT_CTRL()