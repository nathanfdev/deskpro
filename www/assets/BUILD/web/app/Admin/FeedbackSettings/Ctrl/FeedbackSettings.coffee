define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_FeedbackSettings_Ctrl_FeedbackSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_FeedbackSettings_Ctrl_FeedbackSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS    = []

    init: ->
      @$scope.brand_id = @$stateParams.brandId

    initialLoad: ->
      @Api2.sendGet('/settings/brands/'+@$scope.brand_id+'/portal/feedback').then( (res) =>
        @$scope.settings = res.data.data
      )

    save: ->
      @startSpinner('saving')
      @Api2.sendPostJson('/settings/brands/'+@$scope.brand_id+'/portal/feedback', @$scope.settings).then( =>
        @Growl.success("Settings saved")
        @stopSpinner('saving')
      )


  Admin_FeedbackSettings_Ctrl_FeedbackSettings.EXPORT_CTRL()
