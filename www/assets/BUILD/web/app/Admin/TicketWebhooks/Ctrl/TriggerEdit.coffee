define [
  'DeskPRO/Util/Arrays'
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
  'Admin/TicketTriggers/Ctrl/EditBase',
], (
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper,
  Admin_TicketTriggers_Ctrl_EditBase
) ->
  class Admin_TicketWebhooks_Ctrl_TriggerEdit extends Admin_TicketTriggers_Ctrl_EditBase
    @CTRL_ID   = 'Admin_TicketWebhooks_Ctrl_TriggerEdit'
    @CTRL_AS   = 'TicketTriggersEdit'
    @DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions']

    init: ->
      super
      @webhookId   = @$stateParams.webhookId
      @enableEventsSection = false
      @enableCopyFromAnotherTrigger = false
      @dpWebhooks = @DataService.get('WebhookTriggers')

    getPostData: ->
      legacyPostData = super()
      postData = {
        title: legacyPostData.title,
        actions: {
          version:1,
          actions: legacyPostData.actions
        },
        terms: legacyPostData.criteria_sets
      }

      return postData

    onTriggerSave: (trigger) ->
      { has_stop_triggers_action, has_delete_ticket_action } = @getPostActionsDetails()

      @trigger.title = trigger.title
      @trigger.has_stop_triggers_action = has_stop_triggers_action
      @trigger.has_delete_ticket_action = has_delete_ticket_action

      @dpTriggers.mergeDataModel({
        id: @trigger.id,
        title: @trigger.title,
        is_enabled: @trigger.is_enabled,
        has_stop_triggers_action: has_stop_triggers_action
        has_delete_ticket_action: has_delete_ticket_action
      })

    ###
    # Save the trigger
    ###
    saveTrigger: ->
      return if @$scope.form_props.$invalid

      @resetErrors()
      triggerData = @getPostData()

      @startSpinner('saving')
      is_new = !@trigger || !@trigger.id

      if is_new
        promise = @Api2.sendPostJson('/' + ['webhooks', @webhookId, 'triggers'].join('/'), triggerData)
      else
        promise = @Api2.sendPutJson('/' + ['webhooks', @webhookId, 'triggers', @trigger.id].join('/'), triggerData)

      promise = promise.success((response) =>
          trigger = response.data
          @onTriggerSave(trigger)
          return trigger
        ).success( (trigger) =>
          @skipDirtyState()
          @stopSpinner('saving', true).then( => @Growl.success("Saved"))
          if is_new
            # close this view
            window.location.hash = '/webhooks'
            @$state.go('tickets.webhooks')
            @$scope.$parent.List.onTriggerAdded(trigger, @webhookId)
      ).error( (result, code) =>
        @stopSpinner('saving', true)
        if result?.error_code == 'invalid'
          @showErrors(result.error_info)
      )

      return promise


  Admin_TicketWebhooks_Ctrl_TriggerEdit.EXPORT_CTRL()
