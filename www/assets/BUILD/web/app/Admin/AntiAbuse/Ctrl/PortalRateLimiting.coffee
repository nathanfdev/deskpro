define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_AntiAbuse_Ctrl_PortalRateLimiting extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_PortalRateLimiting'
    @CTRL_AS = 'PortalRateLimiting'

    _url = '/v2/settings/anti_abuse/portal'

    init: ->
      @$scope.settings = null
      @$scope.feedback_settings = null
      @$scope.registration_settings = null

    initialLoad: ->
      promise = @Api.sendGet(_url).then (res) =>
        @$scope.settings = res.data.data

      feedbackSettingsPromise = @Api.sendGet('/settings/portal/feedback').then( (res) =>
        @$scope.feedback_settings = res.data.settings
      )

      registrationSettingsPromise = @Api.sendGet('/registration_settings?rate_limit_context=user').then( (res) =>
        @$scope.registration_settings = res.data.registration_settings
      )

      return @$q.all([promise, feedbackSettingsPromise, registrationSettingsPromise])

    save: ->
      @startSpinner('saving')
      @Api.sendPutJson(_url, @$scope.settings).success( =>
        @stopSpinner('saving')
        @Growl.success @getRegisteredMessage('saved_settings')
      ).error( (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_AntiAbuse_Ctrl_PortalRateLimiting.EXPORT_CTRL()
