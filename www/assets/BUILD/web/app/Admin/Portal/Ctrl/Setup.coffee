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
      if (@$scope.brand_id != 'new')
        @portalSettings.setBrandId(@$scope.brand_id)
        @portalSettings.getSettings().then((s) =>
          @settings = s
          if (@settings.brand_logo)
            @Api2.sendGet('/brands/' + @settings.brand).then (res) =>
              @setAvatar res.data.data.logo_blob
              @settings.enable_brand_logo == !!res.data.data.logo_blob

        )

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
      me = @
      @Api2.sendGet('/brands/url/' + encodeURIComponent(@settings.deskpro_url))
      .then (res) =>
        me.Growl.error("Each brand need to have a different url")
        $('#helpdesk_url').focus()
      .catch (err) ->
        me.Api2.sendPostJson('brands', brand).then (res) =>
          me.Growl.success("Brand created")
          me.$scope.brand_id = res.data.data.id
          me.portalSettings.setBrandId(res.data.data.id)
          me.saveSettings().then(=>
            me.$state.go 'portal.setup', {brandId: me.brandId}
          )



    deleteBrand: ->
      if confirm "Are you sure you want to delete this brand? Theme personalization and templates will be lost."
        @Api2.sendDelete('brands/' + @$scope.brand_id).then  =>
          @Growl.success("Brand deleted")
          @$state.go 'portal', {brandId: @$scope.default_brand.id}



    setAvatar: (blob) =>
      @settings.brand_logo = blob.id
      if !blob?
        @$scope.icon_image = null
        @settings.enable_brand_logo = false
      else
        @$scope.icon_image = blob.download_url
        @settings.enable_brand_logo = true



    onFileSelect: (files) ->
      @$scope.uploading = false
      file = files[0]

      @$upload.upload({
        url: @$http.formatApiUrl('/misc/upload'),
        data: { is_image: true },
        file: file
      }).success( (data) =>
        @$scope.uploading = false
        @setAvatar data.blob
      ).error( (data) =>
        @$scope.uploading = false
        @Growl.error data?.error_message || 'Error'
      )



    selectIcon: (image) =>
      setAvatar null if !image?


      @$scope.uploading = true
      @Api.sendPostJson('/misc/upload', {path: image, is_image: true}).then(
        (data) =>
          @$scope.uploading = false
          @setAvatar data.data.blob
        () =>
          @$scope.uploading = false
      )

  Admin_Portal_Ctrl_Setup.EXPORT_CTRL()
