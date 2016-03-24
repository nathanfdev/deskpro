define ['Admin/Main/Ctrl/Base', 'angular'], ( Admin_Ctrl_Base) ->
  class Admin_AntiAbuse_Ctrl_LoginLockoutSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_LoginLockoutSettings'
    @CTRL_AS = 'LoginLockoutSettings'

    _url = '/password_settings'

    init: ->
      @$scope.agent_reg_settings = null
      @$scope.agent_rate_settings = null
      @$scope.user_reg_settings = null
      @$scope.user_rate_settings = null

    initialLoad: ->
      agentsPromise = @Api.sendGet(_url, {rate_limit_context: 'agent'}).then (res) =>
        @$scope.agent_settings = res.data.settings
        @$scope.agent_rate_settings = res.data.rate_limit_settings
      usersPromise = @Api.sendGet(_url, {rate_limit_context: 'user'}).then (res) =>
        @$scope.user_settings = res.data.settings
        @$scope.user_rate_settings = res.data.rate_limit_settings

      return @$q.all([agentsPromise, usersPromise])

    save: ->
      agentsPromise = @Api.sendPostJson(_url, {
        settings: @$scope.agent_settings
        rate_limit_settings: @$scope.agent_rate_settings
        rate_limit_context: 'agent'
      })
      usersPromise = @Api.sendPostJson(_url, {
        settings: @$scope.user_settings
        rate_limit_settings: @$scope.user_rate_settings
        rate_limit_context: 'user'
      })

      @startSpinner('saving')
      @$q.all([agentsPromise, usersPromise]).then( =>
        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      , (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_AntiAbuse_Ctrl_LoginLockoutSettings.EXPORT_CTRL()
