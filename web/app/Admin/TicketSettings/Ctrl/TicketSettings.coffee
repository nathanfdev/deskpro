define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'angular'], (Admin_Ctrl_Base, Util, angular) ->
  class Admin_TicketSettings_Ctrl_TicketSettings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketSettings_Ctrl_TicketSettings'
    @CTRL_AS   = 'TicketSettings'
    @DEPS      = []

    init: ->
      @settings = null

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'settings': '/ticket_settings'
      }).then( (res) =>
        settings = res.data.settings.ticket_settings
        for own k, v of settings.agent_defaults
          if not v then settings.agent_defaults[k] = "0"

        days = [false, false, false, false, false, false]
        for day in settings.working_hours.work_days
          days[day] = true

        settings.working_hours.work_days = days

        @$scope.settings = settings
        @settings = angular.copy(@$scope.settings)
      )

      @headerSortList = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')

          newOrder = []
          $list.find('li').each(->
            newOrder.push($(this).data('value'))
          )

          @$scope.settings.from_email_headers = newOrder
      }

      return @$q.all([data_promise])

    isDirtyState: ->
      if not @settings then return false
      if not angular.equals(@settings, @$scope.settings)
        return true
      else
        return false

    save: ->
      postData = {
        ticket_settings: Util.clone(@$scope.settings, true)
      }

      work_days = []
      for enabled, day in @$scope.settings.working_hours.work_days
        if enabled
          work_days.push(day)

      postData.ticket_settings.working_hours.work_days = work_days

      @startSpinner('saving')
      promise = @Api.sendPostJson('/ticket_settings', postData).success( =>
        @settings = angular.copy(@$scope.settings)

        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_TicketSettings_Ctrl_TicketSettings.EXPORT_CTRL()