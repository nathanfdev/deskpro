define ['Admin/Usersources/Ctrl/EditDeskproInstance', 'DeskPRO/Util/Util']
, (Admin_Usersources_Ctrl_EditDeskproInstance, Util) ->
  class Admin_Usersources_Ctrl_AddDeskproInstance extends Admin_Usersources_Ctrl_EditDeskproInstance
    @CTRL_ID   = 'Admin_Usersources_Ctrl_AddDeskproInstance'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$http', 'dpTemplateManager']

    getInstanceId: -> null
    initialLoad: ->
      @usersource = {
        title: 'Deskpro'
        is_disabled: false
        options: {
          reg_enabled: true
        }
      }

      super()

    doSaveUsersource: ->
      @usersource.is_enabled = !@usersource.is_disabled;
      if @usersource.options.reg_enabled
        @usersource.is_enabled = true
      p = super()
      @$q.all([p, @savePolicySettings(), @saveRegSettings()])

    loadPasswordSettings: ->
      @Api.sendGet('/password_settings', {rate_limit_context: 'user'}).then( (res) =>
        @$scope.policy_settings = {
          sessions_lifetime:              res.data.settings.sessions_lifetime,
          session_keepalive_require_page: res.data.settings.session_keepalive_require_page,
          ip_security_enabled:            res.data.settings.ip_security_enabled,
          ip_security_mode:               res.data.settings.ip_security_mode || 'admins',
          ip_security_whitelist_lifetime: res.data.settings.ip_security_whitelist_lifetime + "",
          disable_notifications:          res.data.settings.disable_notifications,
          enable_agent_rememberme:        res.data.settings.enable_agent_rememberme,
          enable_user_rememberme:         res.data.settings.enable_user_rememberme,
          agent_enable_kb_shortcuts:      res.data.settings.agent_enable_kb_shortcuts,
        }

        @$scope.rate_limit_settings = res.data.rate_limit_settings

        @$scope.policy_agent = res.data.settings.agent
        @$scope.policy_user  = res.data.settings.user

        @$scope.policy_agent.standard_policy = @$scope.policy_agent.min_length == 5 and
            !@$scope.policy_agent.max_age and
            !@$scope.policy_agent.forbid_reuse and
            !@$scope.policy_agent.require_num_uppercase and
            !@$scope.policy_agent.require_num_lowercase and
            !@$scope.policy_agent.require_num_number and
            !@$scope.policy_agent.require_num_symbol

        @$scope.policy_user.standard_policy = @$scope.policy_user.min_length == 5 and
            !@$scope.policy_user.max_age and
            !@$scope.policy_user.forbid_reuse and
            !@$scope.policy_user.require_num_uppercase and
            !@$scope.policy_user.require_num_lowercase and
            !@$scope.policy_user.require_num_number and
            !@$scope.policy_user.require_num_symbol
      )

    loadRegSettings: ->
      @Api.sendGet('/registration_settings', {rate_limit_context: 'user'}).then( (res) =>
        @$scope.settings = res.data.registration_settings
        @settings = angular.copy(@$scope.settings)
        @$scope.rate_limit_settings = res.data.rate_limit_settings
      )

    savePolicySettings: ->

      settings = @$scope.policy_settings
      settings.agent = Util.clone(@$scope.policy_agent, true)
      settings.user  = Util.clone(@$scope.policy_user, true)

      if settings.agent.standard_policy
        settings.agent.min_length = 5
        settings.agent.max_age = 0
        settings.agent.forbid_reuse = false
        settings.agent.require_num_uppercase = 0
        settings.agent.require_num_lowercase = 0
        settings.agent.require_num_number = 0
        settings.agent.require_num_symbol = 0
      if settings.user.standard_policy
        settings.user.min_length = 5
        settings.user.max_age = 0
        settings.user.forbid_reuse = false
        settings.user.require_num_uppercase = 0
        settings.user.require_num_lowercase = 0
        settings.user.require_num_number = 0
        settings.user.require_num_symbol = 0

      delete settings.agent.standard_policy
      delete settings.user.standard_policy

      post =
        settings: settings
        rate_limit_settings: @$scope.rate_limit_settings
        rate_limit_context: 'agent'

      @Api.sendPostJson('/password_settings', post)

    saveRegSettings: ->
      postData = {
        registration_settings: @$scope.settings
        rate_limit_settings: @$scope.rate_limit_settings
        rate_limit_context: 'user'
      }

      @Api.sendPostJson('/registration_settings', postData)

    doSaveUsersource: ->
      postData = {
        title: @usersource.title || 'Deskpro',
        is_enabled: @usersource.is_enabled
        options: @usersource.options
        brands: @$scope.usersource_detailsv2.brands
        is_all_brands: @$scope.usersource_detailsv2.is_all_brands
      }

      @Api2.sendPostJson('/user_sources/' + @usersourceType, postData).then(
        =>
          @listCtrl().refresh()
          @Growl.success @getRegisteredMessage 'saved_settings'
        (res) =>
          msg = @getRegisteredMessage(res.data.error_code) || res.data.error_message || ''
          @Growl.error msg
      )

  Admin_Usersources_Ctrl_AddDeskproInstance.EXPORT_CTRL()
