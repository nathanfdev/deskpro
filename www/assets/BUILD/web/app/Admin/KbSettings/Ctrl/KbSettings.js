define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_KbSettings_Ctrl_KbSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_KbSettings_Ctrl_KbSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS    = []

    init: ->
      @$scope.brand_id = @$stateParams.brandId

    initialLoad: ->
      @Api2.sendGet('/settings/brands/'+@$scope.brand_id+'/portal/kb').then( (res) =>
        @$scope.settings = res.data.data
      )

    save: ->
      @startSpinner('saving')
      @Api2.sendPostJson('/settings/brands/'+@$scope.brand_id+'/portal/kb', @$scope.settings).then( =>
        @Growl.success("Settings saved")
        @stopSpinner('saving')
      )


  Admin_KbSettings_Ctrl_KbSettings.EXPORT_CTRL()