define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_License_Ctrl_License extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_License_Ctrl_License'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$window', 'DpLicense']

    init: ->
      @license          = null
      @ma_token         = null
      @ma_login_url     = null
      @lic_set_callback = null
      @$scope.form = { lic_code: '' }

      @$scope.$watch('form.lic_code', (lic_code) =>
        lic_code = lic_code || ''
        lic_code = lic_code.replace(/\s/g, '')
        lic_code = (lic_code.match(/(.{1,50})/g) || [lic_code]).join("\n")
        @$scope.form.lic_code = lic_code
      )
      old_title = window.document.title
      window.document.title = 'DeskPRO Billing Interface'
      @$scope.$on('$destroy', ->
        window.document.title = old_title
      )

    reloadLicData: ->
      data_promise = @Api.sendDataGet({
        'lic_info': '/dp_license'
      }).then( (res) =>
        @license          = res.data.lic_info.license
        @license.countAgents = res.data.lic_info.limits.count_agents
        @ma_token         = res.data.lic_info.ma_token
        @ma_login_url     = res.data.lic_info.ma_login_url
        @lic_set_callback = res.data.lic_info.lic_set_callback

        @$scope.form.lic_code = @license.licenseCode

        @$scope.refreshing_lic = true
        @DpLicense.getNewLicenseKey().then( (res) =>
          cmp1 = res.license_code.replace(/[^a-zA-Z0-9]/g, '')
          cmp2 = @license.licenseCode.replace(/[^a-zA-Z0-9]/g, '')

          if cmp1 == cmp2
            @$scope.refreshing_lic = false
          else
            @DpLicense.setNewLicenseCode(res.license_code).then( =>
              @$scope.refreshing_lic = false
              lic_code = res.license_code
              lic_code = lic_code.replace(/\s/g, '')
              lic_code = (lic_code.match(/(.{1,50})/g) || [lic_code]).join("\n")
              @$scope.form.lic_code = lic_code
              @license.licenseCode = lic_code
            , =>
              @$scope.refreshing_lic = false
            )
        , =>
          @$scope.refreshing_lic = false
        )

        if res.data.lic_info.custom_billing_frame
          @$scope.custom_billing_frame = res.data.lic_info.custom_billing_frame
          @$scope.iframe_loading = false
          @$scope.iframe_code    = '<iframe src="' + @$scope.custom_billing_frame + '" frameborder="0"></iframe>'
      )

      return data_promise

    initialLoad: ->
      return @reloadLicData()

    downloadKeyfile: ->
      @$window.location = @Api.formatUrl('dp_license/keyfile.txt') + '?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN

    goToMembersArea: ->
      @$window.location = 'https://www.deskpro.com/members/'
      return

    saveLicenseCode: ->
      postData = {
        license_code: @$scope.form.lic_code
      }

      @$scope.lic_error_code = false
      @$scope.show_lic_error = false
      @startSpinner('saving')
      @Api.sendPost("dp_license", postData).success(=>
        @DpLicense.getLicInfo(true).then(
          @reloadLicData().then(=>
            @stopSpinner('saving').then(=>
              @Growl.success(@getRegisteredMessage('saved_lic'))
            )
          )
        )
      ).error( (data) =>
        @stopSpinner('saving', true)
        @$scope.lic_error_code = false
        if data and data.error_code
          @$scope.lic_error_code = data.error_code

        @$scope.show_lic_error = true
      )

    save: ->
      return

    openUpgradeLicense: ->
      @DpLicense.openUpgradeLicense('add_agents').then(=> @$state.go('license_go') )

    openRenewLicense: ->
      @DpLicense.openRenewLicense().then(=> @$state.go('license_go') )

  Admin_License_Ctrl_License.EXPORT_CTRL()