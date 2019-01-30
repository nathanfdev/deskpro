define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit,
)  ->
  class Admin_TicketFilters_DataService_TicketSlas extends BaseListEdit
    @$inject = ['Api', '$q']

    _doLoadList: ->
      deferred = @$q.defer()

      @Api.sendGet('/ticket_slas').success( (data) =>
        models = data.slas
        deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise


    ###
      # Remove an slas
      #
      # @param {Integer} id SLA id
      # @return {promise}
    ###
    deleteSlaById: (id) ->
      promise = @Api.sendDelete('/ticket_slas/' + id).then(=>
        @removeListModelById(id)
      )
      return promise


    ###
      # Get all data needed for the edit filter page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditSlaData: (id) ->

      deferred = @$q.defer()

      @Api.sendGet('/ticket_slas/' + id).then( (result) ->
        deferred.resolve({
          sla: result.data.sla
        })
      , ->
        deferred.reject()
      )

      return deferred.promise