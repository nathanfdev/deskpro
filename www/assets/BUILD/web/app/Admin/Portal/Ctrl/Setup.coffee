define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_Setup extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_Setup'
    @CTRL_AS = 'Ctrl'
    @DEPS = ['$timeout']

    init: ->
      @settings = {}
      @portalSettings = @DataService.get 'PortalGeneralSettings'

      @$scope.brand_id = @$stateParams.brandId
      @$scope.brand = false

      @Api2.sendGet('brands/default').then (res) =>
        @$scope.default_brand = res.data.data

      @$scope.$on 'icon.selected', (e, path) => @selectIcon path

      @$scope.$watch('brand_id', =>
        @portalSettings.setBrandId(@$scope.brand_id)
        @portalSettings.getSettings().then((s) => @settings = s)
      )

      tmpUpdate = => @portalSettings.updateSettingsTemporary(@settings)
      for i in ['apps_feedback', 'apps_kb', 'apps_news', 'apps_downloads', 'iface_portal', 'iface_widget']
        @$scope.$watch('Ctrl.settings.'+i, tmpUpdate)

    setPortalMode: (v) ->
      if v == "publish"
        @settings.portal_mode = "publish"
        @settings.apps_downloads = true
        @settings.apps_feedback = true
        @settings.apps_guides = true
        @settings.apps_kb = true
        @settings.apps_news = true
      else
        @settings.portal_mode = "tickets"
        @settings.apps_downloads = false
        @settings.apps_feedback = false
        @settings.apps_guides = false
        @settings.apps_kb = false
        @settings.apps_news = false

      @portalSettings.updateSettingsTemporary(@settings)

    initialLoad: ->
      promises = []
      if (@$scope.brand_id != 'new')
        @portalSettings.setBrandId(@$scope.brand_id)

        settingPromise = @portalSettings.getSettings()

        settingPromise
          .then((res) => @settings = res)
        promises.push(settingPromise)

        brandPromise = @Api2.sendGet('/brands/' + @$scope.brand_id)
        brandPromise.then (res) =>
          @$scope.brand = res.data.data

        promises.push(brandPromise)

      @$q.all(promises)

    cloudSetupHost: ->

      @$scope.ma_error_message = null
      @$scope.ma_pending_message = null

      d = @$q.defer()

      if !window.DP_IS_CLOUD
        d.resolve()
        return d.promise

      input = @settings.deskpro_url

      parser = document.createElement('a')
      parser.href = input
      domain = parser.hostname

      if !domain
        @Growl.error "You must specify a name and a url"
        $('#helpdesk_name').focus()
        r.reject()
        return

      @settings.deskpro_url = 'https://' + domain + '/'

      if @settings.orig_deskpro_url == @settings.deskpro_url
        d.resolve()
        return d.promise

      @$scope.ma_pending_message = 'Checking your custom domain'

      @setupCustomDomain(domain).then(=>
        d.resolve()
      , =>
        d.reject()
      )

      return d.promise

    setupCustomDomain: (domain) ->
      d = @$q.defer()

      @Api.sendPostJson('/settings/cloud/setup-custom-domain?allowProvider', { domain: domain }).then( (res) =>
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
          @$scope.ma_error_message = null
          @$scope.ma_pending_message = 'Your custom domain has been configured. It might take a few minutes for your domain to become fully functional.'
          d.resolve()
      , =>
        @$scope.form_error = 'server_error'
        d.reject()
      )

      return d.promise

    saveSettings: (skipCloudCheck) ->
      if !@settings.deskpro_url.match(/^https?:\/\//i)
        @settings.deskpro_url = 'https://' + @settings.deskpro_url

      @startSpinner()

      if window.DP_IS_CLOUD
        if skipCloudCheck
          d = @$q.defer()
          d.resolve()
          checkP = d.promise
        else
          checkP = @cloudSetupHost()
      else
        d = @$q.defer()
        d.resolve()
        checkP = d.promise

      checkP.then(=>
        @portalSettings.updateSettings(@settings).then(=>
          @originalUrl == @settings.deskpro_url
          @stopSpinner()
          @$scope.$emit 'dp-update-brands'
        , =>
          @stopSpinner()
        )
      , =>
        @stopSpinner()
      )

    createBrand: ->
      if (!@settings.deskpro_name || !@settings.deskpro_url)
        @Growl.error "You must specify a name and a url"
        $('#helpdesk_name').focus()
        @startSpinner()
        return false

      if !@settings.deskpro_url.match(/^https?:\/\//i)
        @settings.deskpro_url = 'https://' + @settings.deskpro_url

      brand = {
        name: @settings.deskpro_name,
        url: @settings.deskpro_url
      }

      @startSpinner()

      if window.DP_IS_CLOUD
        checkP = @cloudSetupHost().then()
      else
        checkP = @Api2.sendPostJson('/brands/check_url', {url: @settings.deskpro_url})

      checkP.then( (res) =>
        if !res || res.data.data.free
          @Api2.sendPostJson('brands', brand).then (res) =>
            @Growl.success "Brand created"
            @$scope.brand_id = res.data.data.id
            @portalSettings.setBrandId(res.data.data.id)
            @brandId = res.data.data.id
            @saveSettings(true).then(=>
              @stopSpinner()
              @$state.go 'portal.setup', {brandId: @brandId}
            )
          , (res) =>
            @Growl.error res.data.message
            @stopSpinner()
        else if res.data.data.reason
          @Growl.error res.data.data.reason
          $('#helpdesk_url').focus()
          @stopSpinner()
        else
          @Growl.error "Each brand need to have a different url"
          $('#helpdesk_url').focus()
          @stopSpinner()
      , =>
        @stopSpinner()
        @Growl.error "We can't check this url. Try another one or contact your system administrator."
      )

    deleteBrand: ->
      if confirm "Are you sure you want to delete this brand? Deleting the brand will re-assign tickets and chat to the default brand. Theme personalization and templates will be lost."
        @Api2.sendDelete('brands/' + @$scope.brand_id).then  =>
          @Growl.success("Brand deleted")
          @$state.go 'portal.setup', {brandId: @$scope.default_brand.id}
          @$scope.$emit 'dp-update-brands'

  Admin_Portal_Ctrl_Setup.EXPORT_CTRL()
