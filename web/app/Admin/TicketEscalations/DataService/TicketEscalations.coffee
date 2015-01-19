define [
  'Admin/Main/DataService/BaseListEdit',
  'Admin/TicketEscalations/EscalationEditFormMapper'
], (
  BaseListEdit,
  EscalationEditFormMapper
)  ->
  class Admin_TicketFilters_DataService_TicketEscalations extends BaseListEdit
    @$inject = ['Api', '$q']

    _doLoadList: ->
      deferred = @$q.defer()

      @Api.sendGet('/ticket_escalations').success( (data) =>
        models = data.escalations
        deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise


    ###
      # Save the enabled state of a esc
      #
      # @param {Integer} escId
      # @param {bool} isEnabled
      # @return {promise}
    ###
    saveEnabledStateById: (escId, isEnabled) ->
      if isEnabled
        return @Api.sendPost("/ticket_escalations/#{escId}/enable")
      else
        return @Api.sendPost("/ticket_escalations/#{escId}/disable")

    saveEnabledState: (esc) ->
      @saveEnabledStateById esc.id, esc.is_enabled

    ###
      # Save order of escalations
      #
      # @param {Array} orders Array of IDs, in order
      # @return {promise}
    ###
    saveRunOrder: (orders) ->
      for id, idx in orders
        model = @findListModelById(id)
        if model
          model.display_order = idx

      promise = @Api.sendPostJson('/ticket_escalations/run_order', { display_order: orders })
      return promise


    ###
      # Remove a filter
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    deleteEscalationById: (id) ->
      promise = @Api.sendDelete('/ticket_escalations/' + id).then(=>
        @removeListModelById(id)
      )
      return promise


    ###
      # Get the form mapper
      #
      # @return {EscalationEditFormMapper}
    ###
    getFormMapper: ->
      if @formMapper then return @formMapper
      @formMapper = new EscalationEditFormMapper()
      return @formMapper

    ###
      # Get all data needed for the edit filter page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditEscalationData: (id) ->

      deferred = @$q.defer()

      if id
        @Api.sendGet('/ticket_escalations/' + id).then( (result) =>
          data = {}
          data.escalation = result.data.escalation
          data.form = @getFormMapper().getFormFromModel(data.escalation)
          deferred.resolve(data)
        , ->
          deferred.reject()
        )
      else
        data = {}
        data.escalation = {
          id: null,
          title: ''
        }
        data.form = @getFormMapper().getFormFromModel(data.escalation)
        deferred.resolve(data)

      return deferred.promise

    loadEditSatisfactionEscalation: ->
      deferred = @$q.defer()

      @Api.sendGet('/ticket_escalations/satisfaction/0').then(
        (result) =>
          data =
            escalation: result.data.escalation
            form: @getFormMapper().getFormFromModel result.data.escalation
          deferred.resolve(data)
        , ->
          deferred.reject()
      )

      deferred.promise


    ###
      # Saves a form model and applies the form model to the macro model
      # once finished.
      #
      # @param {Object} escModel The esc model
      # @param {Object} formModel  The model representing the form
      # @return {promise}
    ###
    saveFormModel: (escModel, formModel) ->
      mapper = @getFormMapper()

      postData = mapper.getPostDataFromForm(formModel)

      if escModel.id
        promise = @Api.sendPostJson('/ticket_escalations/' + escModel.id, postData)
      else
        promise = @Api.sendPutJson('/ticket_escalations', postData).success( (data) ->
          escModel.id = data.escalation_id
        )

      promise.success(=>
        mapper.applyFormToModel(escModel, formModel)
        @mergeDataModel(escModel)
      )

      return promise