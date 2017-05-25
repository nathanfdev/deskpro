define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Settings_Ctrl_Notifications extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_Notifications'
    @CTRL_AS   = 'Notifications'
    @DEPS      = ['Api2', 'Growl']

    init: ->
      @$scope.pusher_enabled = false;
      return

    initialLoad: ->
      @Api2.sendGet('/notify/setup/action-alerts/pusher').then(
        (response) =>
          pusherModel = response.data.data
          @$scope.pusher_enabled = pusherModel.pusher_enabled
          @$scope.id             = pusherModel.id
          @$scope.secret         = pusherModel.secret
          @$scope.key            = pusherModel.key
      )
      return

    save:  =>
      params = {
        key:    @$scope.key
        id:     @$scope.id
        secret: @$scope.secret
        pusher_enabled: @$scope.pusher_enabled
      }
      @Api2.sendPutJson('/notify/setup/action-alerts/pusher', params).success(
        => @Growl.success('Your request is successful')
      ).error(
        =>
          @Growl.error('Something went wrong. Please contact your administrator')
      )

  Admin_Settings_Ctrl_Notifications.EXPORT_CTRL()