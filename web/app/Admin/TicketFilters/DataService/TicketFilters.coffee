define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit,
)  ->
  class Admin_TicketFilters_DataService_TicketFilters extends BaseListEdit
    @$inject = ['Api2', '$q']

    _doLoadList: ->
      deferred = @$q.defer()

      @Api2.sendGet('/ticket_filters')
        .success( (data) => deferred.resolve(data.data) )
        .error( (data, status, headers, config) => deferred.reject() )

      return deferred.promise


    ###
      # Save order of filters
      #
      # @param {Array} orders Array of IDs, in order
      # @return {promise}
    ###
    saveDisplayOrder: (orders) ->
      ###
      for id, idx in orders
        model = @findListModelById(id)
        if model
          model.display_order = idx
      ###

      @Api2.sendPostJson('/ticket_filters/display_order', { display_order: orders })


    ###
      # Remove a filter
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    deleteFilterId: (id) ->
      promise = @Api.sendDelete('/ticket_filters/' + id).then(=>
        @removeListModelById(id)
      )
      return promise
    
    ###
    # Save a brand new filter.
    ###
    saveFilterData: (filter) ->
      deferred = @$q.defer()
      @Api2.sendPostJson('/ticket_filters', filter).then(
        (data) => deferred.resolve(data.data)
      ,
        (data) => deferred.reject()
      )
      return deferred.promise


    ###
      # Get all data needed for the edit filter page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditFilterData: (id) ->
      deferred = @$q.defer()

      @Api2.sendGet('/ticket_filters/' + id).then(
        (res) => deferred.resolve(res.data.data)
      ,
        (data) => deferred.reject()
      )

      # types = {}
      # if id
      #   types.filter = '/ticket_filters/' + id
      #
      # types.agents = '/agents'
      # types.teams = '/agent_teams'
      #
      # @Api2.sendDataGet(types).then( (res) ->
      #   data = {}
      #   if res.data.filter
      #     data.filter = res.data.filter.filter
      #
      #   data.agents = res.data.agents.agents
      #   data.teams  = res.data.teams.agent_teams
      #   deferred.resolve(data)
      # , -> deferred.reject())

      return deferred.promise
