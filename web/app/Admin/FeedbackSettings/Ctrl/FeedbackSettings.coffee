define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_FeedbackSettings_Ctrl_FeedbackSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_FeedbackSettings_Ctrl_FeedbackSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS    = []

    initialLoad: ->
      @Api.sendGet('/settings/portal/feedback').then( (res) =>
        @$scope.settings = res.data.settings
      )

    save: ->
      postData = {
        settings: @$scope.settings
      }

      @startSpinner('saving')
      @Api.sendPostJson('/settings/portal/feedback', postData).then( =>
        @stopSpinner('saving')
      )


  Admin_FeedbackSettings_Ctrl_FeedbackSettings.EXPORT_CTRL()