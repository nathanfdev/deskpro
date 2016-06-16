define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_Setup extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_Setup'
    @CTRL_AS = 'Ctrl'

    init: ->
      @settings = {}
      @portalSettings = @DataService.get 'PortalGeneralSettings'

      @$scope.brand_id = @$stateParams.brandId

      @Api2.sendGet('brands/default').then (res) =>
        @$scope.default_brand = res.data.data

      @$scope.$watch('Ctrl.portalSettings.version', =>
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
      @portalSettings.getSettings().then((s) =>
        @settings = s
      )

    saveSettings: ->
      @startSpinner()
      @portalSettings.updateSettings(@settings).then(=>
        @stopSpinner()
      , =>
        @stopSpinner()
      )

    deleteBrand: ->
      if confirm "Are you sure you want to delete this brand? Theme personalization and templates will be lost."
        @Api2.sendDelete('brands/' + @$scope.brand_id).then  =>
          @Growl.success("Brand deleted")
          @$state.go 'portal', {brandId: @$scope.default_brand.id}

  Admin_Portal_Ctrl_Setup.EXPORT_CTRL()
