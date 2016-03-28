define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_AntiAbuse_Ctrl_PortalRateLimiting extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_PortalRateLimiting'
    @CTRL_AS = 'PortalRateLimiting'

    _url = '/settings/anti_abuse/portal'

    init: ->
      @$scope.settings = null
      @$scope.feedbackSettings = null
      @$scope.usersourceSettings = null

    initialLoad: ->
      promise = @Api2.sendGet(_url).then (res) =>
        @$scope.settings = res.data.data
      feedbackPromise = @Api.sendGet('/settings/portal/feedback').then (res) =>
        @$scope.feedbackSettings = res.data.settings
      usersourcePromise = @Api2.sendGet('/settings/user_source').then (res) =>
        @$scope.usersourceSettings = res.data.data

      return @$q.all([promise, feedbackPromise, usersourcePromise])

    save: ->
      @startSpinner('saving')
      @Api2.sendPutJson(_url, @$scope.settings).success( =>
        @stopSpinner('saving')
        @Growl.success @getRegisteredMessage('saved_settings')
      ).error( (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_AntiAbuse_Ctrl_PortalRateLimiting.EXPORT_CTRL()
