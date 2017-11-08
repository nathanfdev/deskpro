define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_Setup extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_Setup'
    @CTRL_AS = 'Ctrl'

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

    saveSettings: ->
      @startSpinner()
      @portalSettings.updateSettings(@settings).then(=>
        @stopSpinner()
        @$scope.$emit 'dp-update-brands'
      , =>
        @stopSpinner()
      )

    createBrand: ->
      if (!@settings.deskpro_name || !@settings.deskpro_url)
        @Growl.error "You must specify a name and a url"
        $('#helpdesk_name').focus()
        return false
      brand = {
        name: @settings.deskpro_name,
        url: @settings.deskpro_url
      }
      @Api2.sendPostJson('/brands/check_url', {url: @settings.deskpro_url})
      .then(
        (res) =>
          if res.data.data.free
            @Api2.sendPostJson('brands', brand).then (res) =>
              @Growl.success "Brand created"
              @$scope.brand_id = res.data.data.id
              @portalSettings.setBrandId(res.data.data.id)
              @brandId = res.data.data.id
              @saveSettings().then(=>
                @$state.go 'portal.setup', {brandId: @brandId}
              )
            , (res) =>
              @Growl.error res.data.message
          else if res.data.data.reason
            @Growl.error res.data.data.reason
            $('#helpdesk_url').focus()
          else
            @Growl.error "Each brand need to have a different url"
            $('#helpdesk_url').focus()
        =>
          @Growl.error "We can't check this url. Try another one or contact your system administrator."
      )

    deleteBrand: ->
      if confirm "Are you sure you want to delete this brand? Theme personalization and templates will be lost."
        @Api2.sendDelete('brands/' + @$scope.brand_id).then  =>
          @Growl.success("Brand deleted")
          @$state.go 'portal.setup', {brandId: @$scope.default_brand.id}

  Admin_Portal_Ctrl_Setup.EXPORT_CTRL()
