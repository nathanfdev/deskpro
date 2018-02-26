define [
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Util
) ->
  class Admin_TicketWebhooks_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketWebhooks_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['dpObTypesDefTicketFilter', '$stateParams', 'Api2']

    init: ->
      @webhook     = null
      @webhookId   = parseInt(@$stateParams.id || 0)

      @form = {}
      @filter_criteria = {}
      @criteriaTypeDef = @dpObTypesDefTicketFilter
      @criteriaOptionTypes = @criteriaTypeDef.getOptionsForTypes()
      @webhookUrl = null

      return

    ###
    # Load the trigger
    ###
    initialLoad: ->
      promise = Promise.resolve()
      if @webhookId
        promise = @Api2.sendGet("/webhooks/tickets/#{@webhookId}").then(
          (result) =>
            @webhook = result.data.data
            @webhookUrl = @Api2.buildEndpointAPIUrl("webhooks/#{@webhook.auth_id}/invocation")

            @form = @getFormFromModel(result.data.data)
            @filter_criteria = {}
            if @webhook.search_terms?.length
              for term in @webhook.search_terms
                rowId = Util.uid('term')
                @filter_criteria[rowId] = term
        )

      promise2 = @criteriaTypeDef.loadDataOptions().then(=>
        @criteriaOptionTypes = @criteriaTypeDef.getOptionsForTypes()
      )
      promises = [promise, promise2]
      return @$q.all(promises)

    copyWebhookUrl: () ->
      ev.preventDefault();

    getFormFromModel: (model) ->
      form = {}
      form.title = model.title || ''
      form.payload_decoder = model.payload_decoder || ''

      form.terms_set = {}
      if model.search_terms?.length
        setId = _.uniqueId('termset')
        form.terms_set[setId] = {}

        for term in model.search_terms
          rowId = _.uniqueId('term')
          form.terms_set[setId][rowId] = term

      return form

    saveForm: ->
      if not @$scope.form_props.$valid then return

      data = {
        title: @form.title,
        search_terms: Object.keys(@filter_criteria).map (key) => @filter_criteria[key]
      }

      if @form.payload_decoder?.length
        data.payload_decoder = @form.payload_decoder

      p = null
      if @webhookId
        p = @Api2.sendPutJson("/webhooks/tickets/#{@webhookId}", data)
      else
        p = @Api2.sendPostJson("/webhooks/tickets", data)

        p.then((res) =>
          if not @webhookId
            @webhook = res.data.data
            @webhookId = @webhook.id
          @$scope.$parent.List.onWebhookAdded(@webhook)
          @Growl.success(@getRegisteredMessage('saved_filter'))
        ).catch((res) =>
          @Growl.error res.data?.error_message if res.data?.error_message
        )


  Admin_TicketWebhooks_Ctrl_Edit.EXPORT_CTRL()
