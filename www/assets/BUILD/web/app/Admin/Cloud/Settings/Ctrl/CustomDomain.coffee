define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Cloud_Settings_Ctrl_CustomDomain extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Cloud_Settings_Ctrl_CustomDomain'
    @CTRL_AS   = 'Ctrl'
    @DEPS = ['$timeout']

    initialLoad: ->
      @Api.sendGet('/settings/cloud/url-settings').then( (res) =>
        @$scope.form = res.data.settings
      )

    setupCustomDomain: (domain) ->
      d = @$q.defer()

      @Api.sendPostJson('/settings/cloud/setup-custom-domain', { domain: domain }).then( (res) =>
        console.log(res)

        if res.data.error
          @$scope.ma_pending_message = null
          @$scope.ma_error_message = res.data.message
          d.reject()
        else if !res.data.error && !res.data.domain_id
          @$scope.ma_error_message = null
          @$scope.ma_pending_message = res.data.message
          @$timeout(=>
            @setupCustomDomain(domain).then(=>
              d.resolve()
            , =>
              d.reject()
            )
          , 3000)
        else
          @$scope.ma_pending_message = null
          @$scope.ma_pending_message = 'Your custom domain has been configured. It might take a few minutes for your domain to become fully functional.'
          d.resolve()
      , =>
        @$scope.form_error = 'server_error'
        d.reject()
      )

      return d.promise

    save: ->
      @$scope.form_error = null
      @startSpinner('saving')
      postData = {
        settings: @$scope.form
      }

      d = @$q.defer()

      @$scope.ma_pending_message = null
      @$scope.ma_error_message = null

      if postData.settings.domain_choice == 'custom'
        @$scope.ma_pending_message = 'Checking your custom domain'
        @setupCustomDomain(postData.settings.cloud_custom_domain).then(=>
          d.resolve()
        , =>
          d.reject()
        )
      else
        d.resolve()

      d.promise.then(=>
        @Api.sendPostJson('/settings/cloud/url-settings', postData).then(=>
          @stopSpinner('saving').then(=>
            @Growl.success(@getRegisteredMessage('saved_settings'))
          )
        , (res) =>
          @stopSpinner('saving', true)
          if res.data?.error_code?
            @$scope.form_error = res.data.error_code
          else
            @$scope.form_error = 'server_error'
        )
      , =>
        @stopSpinner('saving', true)
      )

  Admin_Cloud_Settings_Ctrl_CustomDomain.EXPORT_CTRL()