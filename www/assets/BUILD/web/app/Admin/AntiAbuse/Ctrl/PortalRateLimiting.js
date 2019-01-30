define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_AntiAbuse_Ctrl_PortalRateLimiting extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_PortalRateLimiting'
    @CTRL_AS = 'PortalRateLimiting'

    _url = '/settings/anti_abuse/portal'

    init: ->
      @$scope.settings = null
      @$scope.feedbackSettings = null
      @$scope.usersourceSettings = null
      @$scope.general_settings = null

    initialLoad: ->
      promise = @Api2.sendGet(_url).then (res) =>
        @$scope.settings = res.data.data
      feedbackPromise = @Api.sendGet('/settings/portal/feedback').then (res) =>
        @$scope.feedbackSettings = res.data.settings
      usersourcePromise = @Api2.sendGet('/settings/user_source').then (res) =>
        @$scope.usersourceSettings = res.data.data
      generalPromise = @Api.sendGet('/general_settings').then (res) =>
        @$scope.general_settings = res.data.general_settings

      return @$q.all([promise, feedbackPromise, usersourcePromise, generalPromise])

    save: ->
      promise = @Api2.sendPutJson(_url, @$scope.settings)
      generalPromise = @Api.sendPostJson('/general_settings', {
        general_settings: @$scope.general_settings
      })

      @startSpinner('saving')

      @$q.all([promise, generalPromise]).then( =>
        @stopSpinner('saving')
        @Growl.success @getRegisteredMessage('saved_settings')
      , (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_AntiAbuse_Ctrl_PortalRateLimiting.EXPORT_CTRL()
