define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_TicketAccounts_Ctrl_Settings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketAccounts_Ctrl_Settings'
    @CTRL_AS   = 'Settings'
    @DEPS      = []

    _url = '/email_accounts/settings'

    init: ->
      @$scope.settings = null

    initialLoad: ->
      @Api.sendGet(_url).then (res) =>
        @$scope.settings = res.data.email_settings
        @$scope.maxUploadSize = res.data.max_filesize

        if @$scope.settings.attach_user_must_exts.length
          @$scope.attach_user_exts_limitmode = 'allow'
        else if @$scope.settings.attach_user_not_exts.length
          @$scope.attach_user_exts_limitmode = 'disallow'
        else
          @$scope.attach_user_exts_limitmode = 'any'

        if @$scope.settings.attach_agent_must_exts.length
          @$scope.attach_agent_exts_limitmode = 'allow'
        else if @$scope.settings.attach_agent_not_exts.length
          @$scope.attach_agent_exts_limitmode = 'disallow'
        else
          @$scope.attach_agent_exts_limitmode = 'any'

        if @$scope.settings.core_tickets_reject_spf_level
          @$scope.settings.core_tickets_reject_fail_spf = true

        if @$scope.settings.core_tickets_reject_dkim_level
          @$scope.settings.core_tickets_reject_fail_dkim = true



    save: ->
      if @$scope.attach_user_exts_limitmode == 'allow'
        @$scope.settings.attach_user_not_exts = []
      else if @$scope.attach_user_exts_limitmode == 'disallow'
        @$scope.settings.attach_user_must_exts = []
      else
        @$scope.settings.attach_user_not_exts = []
        @$scope.settings.attach_user_must_exts = []

      if @$scope.attach_agent_exts_limitmode == 'allow'
        @$scope.settings.attach_agent_not_exts = []
      else if @$scope.attach_agent_exts_limitmode == 'disallow'
        @$scope.settings.attach_agent_must_exts = []
      else
        @$scope.settings.attach_agent_not_exts = []
        @$scope.settings.attach_agent_must_exts = []

      if !@$scope.settings.core_tickets_reject_fail_spf
        @$scope.settings.core_tickets_reject_spf_level = null

      if !@$scope.settings.core_tickets_reject_spf_level
        @$scope.settings.core_tickets_reject_fail_spf = false

      if !@$scope.settings.core_tickets_reject_fail_dkim
        @$scope.settings.core_tickets_reject_dkim_level = null

      if !@$scope.settings.core_tickets_reject_dkim_level
        @$scope.settings.core_tickets_reject_fail_dkim = false

      postData = {
        settings: @$scope.settings
      }

      @startSpinner('saving')
      @Api.sendPutJson(_url, postData).success( =>
        @stopSpinner('saving')
        @Growl.success @getRegisteredMessage('saved_settings')
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )



  Admin_TicketAccounts_Ctrl_Settings.EXPORT_CTRL()
