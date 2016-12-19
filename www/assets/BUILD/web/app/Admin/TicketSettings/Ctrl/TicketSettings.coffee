define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'angular'], (Admin_Ctrl_Base, Util, angular) ->
  class Admin_TicketSettings_Ctrl_TicketSettings extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketSettings_Ctrl_TicketSettings'
    @CTRL_AS   = 'TicketSettings'
    @DEPS      = ['$modal']

    init: ->
      @settings = null
      @$scope.escalation_days = 3

      @$scope.editSatisfactionTemplate = =>
        @$modal.open({
          templateUrl: DP_BASE_ADMIN_URL+'/load-view/Templates/modal-email-editor.html',
          controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
          resolve:
            templateName: -> 'DeskPRO:emails_user:ticket-rate.html.twig'
        })

      @$scope.$watch(
        =>
          @$scope.settings?.timelog_autostart
        , (newVal, oldVal) =>
          @$scope.settings.billing_on_reply = false if newVal == false
      )

      @$scope.lock_timeouts = [
        {id:900, label:"15 minutes"},
        {id:1800, label:"30 minutes"},
        {id:3600, label:"1 hour"},
        {id:7200, label:"2 hours"},
        {id:14400, label:"4 hours"},
        {id:28000, label:"8 hours"},
        {id:86400, label:"1 day"}
      ]

      @$scope.digits = []
      for i in [0..8]
        if (i != 1)
          @$scope.digits.push {id: i, label: i + " digits"}
        else
          @$scope.digits.push {id: i, label: i + " digit"}


    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'settings': '/ticket_settings'
      }).then( (res) =>
        settings = res.data.settings.ticket_settings
        for own k, v of settings.agent_defaults
          if not v then settings.agent_defaults[k] = "0"

        days = [null, false, false, false, false, false, false, false]
        for day in settings.working_hours.work_days
          days[day] = true

        settings.working_hours.work_days = days

        @$scope.settings = settings
        @settings = angular.copy(@$scope.settings)
      )

      @headerSortList =
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')

          newOrder = []
          $list.find('li').each(->
            newOrder.push($(this).data('value'))
          )

          @$scope.settings.from_email_headers = newOrder

      @$q.all [data_promise]

    isDirtyState: ->
      return false
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

      @$scope.$broadcast 'trigger.save'

  Admin_TicketSettings_Ctrl_TicketSettings.EXPORT_CTRL()