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
      else
        @settings.portal_mode = "tickets"

      @portalSettings.updateSettingsTemporary(@settings)

    initialLoad: ->
      promises = []
      if (@$scope.brand_id != 'new')
        @portalSettings.setBrandId(@$scope.brand_id)

        settingPromise = @portalSettings.getSettings()
        settingPromise.then (res) =>
          @settings = res
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
        @Growl.error("You must specify a name and a url")
        $('#helpdesk_name').focus()
        return false
      brand = {
        name: @settings.deskpro_name,
        url: @settings.deskpro_url
      }
      @Api2.sendGet('/brands/check_url/' + encodeURIComponent(@settings.deskpro_url))
      .then (res) =>
        if res.data.data.free
          @Api2.sendPostJson('brands', brand).then (res) =>
            @Growl.success("Brand created")
            @$scope.brand_id = res.data.data.id
            @portalSettings.setBrandId(res.data.data.id)
            @brandId = res.data.data.id
            @saveSettings().then(=>
              @$state.go 'portal', {brandId: @brandId}
            )
        else
          @Growl.error("Each brand need to have a different url")
          $('#helpdesk_url').focus()

    deleteBrand: ->
      if confirm "Are you sure you want to delete this brand? Theme personalization and templates will be lost."
        @Api2.sendDelete('brands/' + @$scope.brand_id).then  =>
          @Growl.success("Brand deleted")
          @$state.go 'portal', {brandId: @$scope.default_brand.id}

  Admin_Portal_Ctrl_Setup.EXPORT_CTRL()
