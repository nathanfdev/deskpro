define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], (Admin_Ctrl_Base, Util) ->
  class Admin_Settings_Ctrl_PasswordSettings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_PasswordSettings'
    @CTRL_AS   = 'Settings'
    @DEPS      = ['$upload', '$http']

    init: ->
      @$scope.password_settings = {}

    initialLoad: ->
      @Api.sendGet('/general_settings/blob').then (res) =>
        @$scope.logo_blob = res.data

      data_promise = @Api.sendGet('/password_settings', {rate_limit_context: 'agent'}).then( (res) =>
        @initPolicy(res.data)
      )

      @$scope.ip_white_listing_times = [
        {id: 86400, label: "1 day"},
        {id: 259200, label: "3 days"},
        {id: 432000, label: "5 days"},
        {id: 604800, label: "1 week"},
        {id: 1209600, label: "2 weeks"},
        {id: 1814400, label: "3 weeks"},
        {id: 2592000, label: "1 month"},
        {id: 5184000, label: "2 months"},
        {id: 7776000, label: "3 months"},
        {id: 15552000, label: "6 months"},
        {id: 23328000, label: "9 months"},
        {id: 31536000, label: "1 year"},
        {id: 63072000, label: "2 years"}
      ]

      return data_promise


    initPolicy: (data) =>
      @$scope.settings = {
        sessions_lifetime:              data.settings.sessions_lifetime,
        session_keepalive_require_page: data.settings.session_keepalive_require_page,
        ip_security_enabled:            data.settings.ip_security_enabled,
        ip_security_mode:               data.settings.ip_security_mode || 'admins',
        ip_security_whitelist_lifetime: data.settings.ip_security_whitelist_lifetime + "",
        disable_notifications:          data.settings.disable_notifications,
        enable_agent_rememberme:        data.settings.enable_agent_rememberme,
        enable_user_rememberme:         data.settings.enable_user_rememberme,
        agent_enable_kb_shortcuts: data.settings.agent_enable_kb_shortcuts,
      }

      @$scope.rate_limit_settings = data.rate_limit_settings

      @$scope.agent = data.settings.agent
      @$scope.user  = data.settings.user

      @$scope.agent.standard_policy = @$scope.agent.min_length == 5 and
          !@$scope.agent.max_age and
          !@$scope.agent.forbid_reuse and
          !@$scope.agent.require_num_uppercase and
          !@$scope.agent.require_num_lowercase and
          !@$scope.agent.require_num_number and
          !@$scope.agent.require_num_symbol

      @$scope.user.standard_policy = @$scope.user.min_length == 5 and
          !@$scope.user.max_age and
          !@$scope.user.forbid_reuse and
          !@$scope.user.require_num_uppercase and
          !@$scope.user.require_num_lowercase and
          !@$scope.user.require_num_number and
          !@$scope.user.require_num_symbol

    saveSettings: ->
      return if @$scope.form_props.$invalid
      @startSpinner('saving')

      settings = @$scope.settings
      settings.agent = Util.clone(@$scope.agent, true)
      settings.user  = Util.clone(@$scope.user, true)

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

      @Api.sendPostJson('/password_settings', post).success((data) =>
        @stopSpinner('saving').then(=>
          @initPolicy(data)
          message = @getRegisteredMessage('saved_settings')
          @Growl.success(message) if message && message.length
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
      )


    onFileSelect: (files) ->
      @$scope.logo_uploading = true
      file = files[0]

      @$upload.upload({
        url: @$http.formatApiUrl '/blobs'
        file: file
      }).success( (data) =>
        @$scope.logo_uploading = false
        @Api.sendPost('/general_settings/blob', {blob_id: data.blob.id}).then (res) =>
          @$scope.logo_blob = res.data
      ).error( (data) =>
        @$scope.logo_uploading = false
        @Growl.error data?.error_message || 'Error'
      )

  Admin_Settings_Ctrl_PasswordSettings.EXPORT_CTRL()