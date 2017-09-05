define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Settings_Ctrl_Notifications extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_Notifications'
    @CTRL_AS   = 'Notifications'
    @DEPS      = ['Api2', 'Growl']

    init: ->
      @$scope.mode = 'db';
      @$scope.deskpro = {};
      @$scope.pusher = {
        clusters: [
          { title: 'mt1 (us-west-1)', value: 'mt1' }
          { title: 'eu (eu-west-1)', value: 'eu' }
          { title: 'ap1 (ap-southwest-1)', value: 'ap1' }
          { title: 'ap2 (ap-south-1)', value: 'ap2' }
        ]
      };
      return

    initialLoad: ->
      return @Api2.sendGet('/notify/setup/action-alerts/clients').then( (response) =>
          clients = response.data.data
          @$scope.mode                  = clients.mode

          @$scope.pusher.id             = clients.pusher.id
          @$scope.pusher.secret         = clients.pusher.secret
          @$scope.pusher.key            = clients.pusher.key
          @$scope.pusher.currentCluster = clients.pusher.cluster

          @$scope.deskpro.secret         = clients.deskpro.secret
          @$scope.deskpro.host           = clients.deskpro.host
          @$scope.deskpro.port           = clients.deskpro.port
      )

    getPusherParams: ->
      params = {
        key:     @$scope.pusher.key
        id:      @$scope.pusher.id
        secret:  @$scope.pusher.secret
        cluster: @$scope.pusher.currentCluster
      }
      return params

    getDeskproParams: ->
      params = {
        port:   @$scope.deskpro.port
        host:   @$scope.deskpro.host
        secret: @$scope.deskpro.secret
      }
      return params

    save: =>

      if(@$scope.mode == 'deskpro')
        params = @getDeskproParams()
      else if (@$scope.mode == 'pusher')
        params = @getPusherParams()
      else
        params = {}

      params.mode = @$scope.mode

      @Api2.sendPutJson('/notify/setup/action-alerts/clients', params).success(
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

    testDeskpro: =>
      @$scope.deskproTestResult = "Testing settings ..."

      params = @getDeskproParams()
      @Api2.sendPostJson('/notify/setup/action-alerts/deskpro/test', params).then( (res) =>
        if res.data.success
          @$scope.deskproTestResult = "Success. Settings are OK.\n\n----- Log -----\n\n" + res.data.message
        else
          @$scope.deskproTestResult = "FAILED. Settings are INVALID..\n\n----- Log -----\n\n" + res.data.message
      , =>
        @$scope.deskproTestResult = "FAILED :: The test did not complete successfully"
      )

  Admin_Settings_Ctrl_Notifications.EXPORT_CTRL()