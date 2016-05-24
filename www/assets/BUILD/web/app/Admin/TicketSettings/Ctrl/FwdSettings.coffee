define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Util, Arrays) ->
  class Admin_TicketSettings_Ctrl_FwdSettings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketSettings_Ctrl_FwdSettings'
    @CTRL_AS   = 'Fwd'
    @DEPS      = []

    init: ->
      @default_fwd_regex = '/^(FW|FWD|VL|WG|FS|VB|RV|VS|TR):/i'
      @email_accounts = []
      @$scope.$watch('settings.use_account', (accId) =>
        accId = parseInt(accId)
        a = Arrays.find(@email_accounts, (a) -> a.id == accId)
        @$scope.use_account_address = if a then a.address else "noreply@example.com"
      )

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'settings': '/ticket_settings/fwd',
        'accounts': '/email_accounts'
      }).then( (res) =>
        @email_accounts = res.data.accounts.email_accounts
        @$scope.settings = res.data.settings.ticket_fwd_settings

        if @$scope.settings.agent_fwd_subject_regex
          @$scope.use_agent_fwd_subject_regex = true
        else
          @$scope.use_agent_fwd_subject_regex = false
          @$scope.settings.agent_fwd_subject_regex = @default_fwd_regex

        @settings = Util.clone(@$scope.settings)

        @$scope.settings.use_account = (@$scope.settings.use_account || 0)+""
      )

      return @$q.all([data_promise])

    isDirtyState: ->
      return false
      if not @settings then return false
      if not Util.equals(@settings, @$scope.settings)
        return true
      else
        return false

    save: ->

      postData = {
        ticket_fwd_settings: Util.clone(@$scope.settings)
      }

      if not @$scope.use_agent_fwd_subject_regex or @$scope.settings.agent_fwd_subject_regex == @default_fwd_regex
        postData.ticket_fwd_settings.agent_fwd_subject_regex = null

      @startSpinner('saving')
      promise = @Api.sendPostJson('/ticket_settings/fwd', postData).success( =>
        @settings = Util.clone(@$scope.settings)

        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_TicketSettings_Ctrl_FwdSettings.EXPORT_CTRL()