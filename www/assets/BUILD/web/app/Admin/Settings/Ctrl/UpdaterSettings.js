define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base) ->
  class Admin_Settings_Ctrl_UpdaterSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Settings_Ctrl_UpdaterSettings'
    @CTRL_AS = 'Ctrl'
    @DEPS = []

    init: ->
      @settings = null
      @info = null
      @didManualSet = false
      @manualForm = {
        delay: "60"
      }

    initialLoad: ->
      p1 = @Api2.sendGet('/helpdesk/updater/settings').then( (response) =>
        @settings = response.data.data
      )
      p2 = @Api2.sendGet('/helpdesk/updater/status').then( (response) =>
        @info = response.data.data
      )
      return @$q.all([p1, p2])

    save: ->
      postData = @settings

      @startSpinner('saving')
      @Api2.sendPutJson('/helpdesk/updater/settings', postData).success( =>
        @initialLoad().then( =>
          @stopSpinner('saving').then(=>
            @Growl.success(@getRegisteredMessage('saved_settings'))
          )
        )
      ).error((info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

    doManualSchedule: ->
      postData = {
        delay: parseInt(@manualForm.delay) || 0
      }

      @startSpinner('saving_manual')
      @Api2.sendPostJson('/helpdesk/updater/manual-schedule', postData).success( =>
        @initialLoad().then( =>
          @didManualSet = true
          @stopSpinner('saving_manual').then(=>
            @Growl.success(@getRegisteredMessage('saved_settings'))
          )
        )
      ).error((info, code) =>
        @stopSpinner('saving_manual', true)
        @applyErrorResponseToView(info)
      )

  Admin_Settings_Ctrl_UpdaterSettings.EXPORT_CTRL()
