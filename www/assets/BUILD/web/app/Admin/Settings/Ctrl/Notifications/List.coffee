define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Settings_Ctrl_Notifications extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_Notifications'
    @CTRL_AS   = 'Notifications'
    @DEPS      = ['Api2', 'Growl']

    init: ->
      @$scope.mode = 'default';
      return

    initialLoad: ->
      @Api2.sendGet('/notify/setup/action-alerts/pusher').then(
        (response) =>
          pusherModel = response.data.data
          @$scope.mode           = if pusherModel.pusher_enabled then 'pusher' else 'default'
          @$scope.id             = pusherModel.id
          @$scope.secret         = pusherModel.secret
          @$scope.key            = pusherModel.key
      )
      return

    getPusherParams: ->
      params = {
        key:    @$scope.key
        id:     @$scope.id
        secret: @$scope.secret
        pusher_enabled: @$scope.mode == 'pusher'
      }
      return params

    save:  =>
      params = @getPusherParams()
      @Api2.sendPutJson('/notify/setup/action-alerts/pusher', params).success(
        => @Growl.success('Your request is successful')
      ).error(
        =>
          @Growl.error('Something went wrong. Please contact your administrator')
      )

    testPusher: =>
      @$scope.pusherTestResult = "Testing settings ..."

      params = @getPusherParams()
      @Api2.sendPostJson('/notify/setup/action-alerts/pusher/test', params).then( (res) =>
        if res.data.success
          @$scope.pusherTestResult = "Success. Settings are OK.\n\n----- Log -----\n\n" + res.data.message
        else
          @$scope.pusherTestResult = "FAILED. Settings are INVALID..\n\n----- Log -----\n\n" + res.data.message
      , =>
        @$scope.pusherTestResult = "FAILED :: The test did not complete successfully"
      )

  Admin_Settings_Ctrl_Notifications.EXPORT_CTRL()