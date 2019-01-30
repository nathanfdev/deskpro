define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_DownloadsSettings_Ctrl_DownloadsSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_DownloadsSettings_Ctrl_DownloadsSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS    = []

    init: ->
      @$scope.brand_id = @$stateParams.brandId

    initialLoad: ->
      @Api2.sendGet('/settings/brands/'+@$scope.brand_id+'/portal/downloads').then( (res) =>
        @$scope.settings = res.data.data
      )

    save: ->
      @startSpinner('saving')
      @Api2.sendPostJson('/settings/brands/'+@$scope.brand_id+'/portal/downloads', @$scope.settings).then( =>
        @Growl.success("Settings saved")
        @stopSpinner('saving')
      )


  Admin_DownloadsSettings_Ctrl_DownloadsSettings.EXPORT_CTRL()