define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_GuidesSettings_Ctrl_GuidesSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_GuidesSettings_Ctrl_GuidesSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS    = []

    init: ->
      @$scope.brand_id = @$stateParams.brandId

    initialLoad: ->
      @Api2.sendGet('/settings/brands/'+@$scope.brand_id+'/portal/guides').then( (res) =>
        @$scope.settings = res.data.data
      )

    save: ->
      @startSpinner('saving')
      @Api2.sendPostJson('/settings/brands/'+@$scope.brand_id+'/portal/guides', @$scope.settings).then( =>
        @Growl.success("Settings saved")
        @stopSpinner('saving')
      )


  Admin_GuidesSettings_Ctrl_GuidesSettings.EXPORT_CTRL()
