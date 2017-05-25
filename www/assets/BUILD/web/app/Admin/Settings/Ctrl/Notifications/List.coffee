define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Settings_Ctrl_Notifications extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_Notifications'
    @CTRL_AS   = 'Notifications'
    @DEPS      = ['Api2', 'Growl']

    init: ->
      @$scope.pusher_enabled = false;
      return

    initialLoad: ->
      @Api2.sendGet('/notify/setup/action-alerts').then(
        (response) =>
          clients = response.data.data.clients
          if clients.length == 1 and clients[0].type == 'pusher'
            @$scope.pusher_enabled = true
        @$scope.$watch('pusher_enabled', @togglePusher )
      )
      return

    togglePusher: (newVal, oldVal) =>
      if newVal != oldVal
        @Api2.sendPutJson('/notify/setup/action-alerts', {'pusher_enabled': newVal}).success(
          => @Growl.success('Your request is successful')
          if newVal == true
            @$state.go('server.notifications.pusher')
        ).error(
          =>
            @Growl.error('Something went wrong. Please contact your administrator')
            @$scope.pusher_enabled = oldVal
        )




  Admin_Settings_Ctrl_Notifications.EXPORT_CTRL()