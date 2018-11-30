define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit,
)  ->
  class Admin_TicketStatuses_DataService_TicketStatuses extends BaseListEdit
    @$inject = ['Api2', '$q']

    _doLoadList: ->
      deferred = @$q.defer()

      @Api2.sendGet('/ticket_statuses', []).then( (response) =>
        models = response.data.data
        deferred.resolve(models)
      , (response, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise

    ###
      # @param {Integer} id
      # @return {promise}
    ###
    deleteStatusById: (id) ->
      promise = @Api2.sendDelete('/ticket_statuses/' + id).then(=>
        @removeListModelById(id)
      )
      return promise


    ###
      # Get all data needed for the edit
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditStatusData: (id) ->
      deferred = @$q.defer()

      @Api2.sendGet('/ticket_statuses/' + id).then( (response) ->
        deferred.resolve({
          status: response.data.data
        })
      , ->
        deferred.reject()
      )

      return deferred.promise