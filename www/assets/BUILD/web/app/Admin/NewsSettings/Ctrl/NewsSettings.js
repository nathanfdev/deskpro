define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_NewsSettings_Ctrl_NewsSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_NewsSettings_Ctrl_NewsSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS    = []

    init: ->
      @$scope.brand_id = @$stateParams.brandId

    initialLoad: ->
      @Api2.sendGet('/settings/brands/'+@$scope.brand_id+'/portal/news').then( (res) =>
        @$scope.settings = res.data.data
      )

    save: ->
      @startSpinner('saving')
      @Api2.sendPostJson('/settings/brands/'+@$scope.brand_id+'/portal/news', @$scope.settings).then( =>
        @Growl.success("Settings saved")
        @stopSpinner('saving')
      )

  Admin_NewsSettings_Ctrl_NewsSettings.EXPORT_CTRL()